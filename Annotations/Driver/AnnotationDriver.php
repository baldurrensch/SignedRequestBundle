<?php

namespace BR\SignedRequestBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use BR\SignedRequestBundle\Annotations\SignedRequest;
use BR\SignedRequestBundle\EventListener\SignedRequestListener;
use BR\SignedRequestBundle\Service\SigningServiceInterface;

/**
 * Annotation driver to support request signing via annotation
 *
 * @author Dirk Pahl <dirk.pahl@motain.de>
 *
 * created 01.07.13 18:07
 */
class AnnotationDriver
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $salt;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $response;

    /**
     * @var SigningServiceInterface
     */
    private $signingService;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var boolean
     */
    private $debug;

    public function __construct(Reader $reader, $salt, $statusCode, $response, EventDispatcher $eventDispatcher, SigningServiceInterface $signingService, $debug = false)
    {
        $this->reader = $reader;
        $this->salt = $salt;
        $this->statusCode = $statusCode;
        $this->response = $response;
        $this->eventDispatcher = $eventDispatcher;
        $this->signingService = $signingService;
        $this->debug = $debug;
    }

    /**
     * Event to fire during any controller call
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) { //return if no controller
            return;
        }

        list($object, $method) = $controller;

        // the controller could be a proxy, e.g. when using the JMSSecurityExtraBundle or JMSDiExtraBundle
        $className = ClassUtils::getClass($object);

        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethod = $reflectionClass->getMethod($method);

        $allAnnotations = $this->reader->getMethodAnnotations($reflectionMethod);

        $signedAnnotations = array_filter($allAnnotations, function($annotation) {
            return $annotation instanceof SignedRequest;
        });

        foreach ($signedAnnotations as $signedAnnotation) {


            $signedRequestListener = new SignedRequestListener($this->salt, $this->statusCode, $this->response, $this->eventDispatcher, $this->debug);
            $signedRequestListener->setSigningService($this->signingService);

            $hashed = $this->signingService->createRequestSignature($event->getRequest(), $this->salt);
            $hashFromRequest = $event->getRequest()->headers->get('X-SignedRequest');

            if ($hashed != $hashFromRequest) {
                if (!$this->debug) {
                    $response = new Response($this->response, $this->statusCode);
                    $response->send();
                } else {
                    $this->eventDispatcher->addListener(KernelEvents::RESPONSE, array($signedRequestListener, 'addDebugResponseMismatch'));
                }
            } else {
                $this->eventDispatcher->addListener(KernelEvents::RESPONSE, array($signedRequestListener, 'addDebugResponseMatch'));
            }
        }
    }
}
