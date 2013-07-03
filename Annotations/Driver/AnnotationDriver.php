<?php

namespace BR\SignedRequestBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use BR\SignedRequestBundle\Annotations\SignedRequest;
use BR\SignedRequestBundle\EventListener\SignedRequestListener;

/**
 * Annotation driver to support request signing via annotation
 *
 * @author Dirk Pahl <dirk.pahl@motain.de>
 * @author Baldur Rensch <brensch@gmail.com>
 */
class AnnotationDriver
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var boolean
     */
    private $debug;

    /**
     * @param Reader                $reader
     * @param SignedRequestListener $signedRequestListener
     * @param bool                  $debug
     */
    public function __construct(Reader $reader, SignedRequestListener $signedRequestListener, $debug = false)
    {
        $this->reader = $reader;
        $this->signedRequestListener = $signedRequestListener;
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
            $getResponseEvent = new GetResponseEvent($event->getKernel(), $event->getRequest(), $event->getRequestType());
            $this->signedRequestListener->onKernelRequest($getResponseEvent);

            if (!$this->debug) {
                if ($response = $getResponseEvent->getResponse()) {
                    $event->setController(
                        function() use ($response) {
                            return $response;
                        }
                    );
                }
            }
        }
    }
}
