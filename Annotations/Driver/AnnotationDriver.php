<?php

namespace BR\SignedRequestBundle\Annotations\Driver;

use BR\SignedRequestBundle\Annotations\SignedResponse;
use BR\SignedRequestBundle\EventListener\SignedResponseListener;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use BR\SignedRequestBundle\Annotations\SignedRequest;
use BR\SignedRequestBundle\EventListener\SignedRequestListener;
use Symfony\Component\HttpKernel\KernelEvents;

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
     * @var SignedRequestListener
     */
    private $signedRequestListener;

    /**
     * @var SignedResponseListener
     */
    private $signedResponseListener;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param Reader                 $reader
     * @param SignedRequestListener  $signedRequestListener
     * @param SignedResponseListener $signedResponseListener
     * @param EventDispatcher        $eventDispatcher
     * @param bool                   $debug
     */
    public function __construct(
        Reader $reader,
        SignedRequestListener $signedRequestListener,
        SignedResponseListener $signedResponseListener,
        EventDispatcher $eventDispatcher,
        $debug = false
    ) {
        $this->reader = $reader;
        $this->signedRequestListener = $signedRequestListener;
        $this->signedResponseListener = $signedResponseListener;
        $this->eventDispatcher = $eventDispatcher;
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
            return ($annotation instanceof SignedRequest || $annotation instanceof SignedResponse);
        });

        foreach ($signedAnnotations as $signedAnnotation) {
            if ($signedAnnotation instanceof SignedRequest) {
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
            } elseif ($signedAnnotation instanceof SignedResponse) {
                $this->eventDispatcher->addListener(KernelEvents::RESPONSE, array($this->signedResponseListener, 'onKernelResponse'));
            }
        }
    }
}
