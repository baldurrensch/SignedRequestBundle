<?php

namespace BR\SignedRequestBundle\EventListener;

use BR\SignedRequestBundle\Service\SigningServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class SignedRequestListener
{
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
     * @var \BR\SignedRequestBundle\Service\SigningServiceInterface
     */
    private $signingService;

    /**
     * @var boolean
     */
    private $debug;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param string          $salt
     * @param int             $statusCode
     * @param string          $response
     * @param EventDispatcher $eventDispatcher
     * @param bool            $debug
     */
    public function __construct($salt, $statusCode, $response, EventDispatcher $eventDispatcher, $debug = false)
    {
        $this->salt = $salt;
        $this->statusCode = $statusCode;
        $this->response = $response;
        $this->debug = $debug;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $hashed = $this->signingService->createRequestSignature($event->getRequest(), $this->salt);
        $hashFromRequest = $event->getRequest()->headers->get('X-SignedRequest');

        if ($hashed != $hashFromRequest) {
            if (!$this->debug) {
                $event->setResponse(new Response($this->response, $this->statusCode));
            } else {
                $this->eventDispatcher->addListener(KernelEvents::RESPONSE, array($this, 'addDebugResponseMismatch'));
            }
        } else {
            if ($this->debug) {
                $this->eventDispatcher->addListener(KernelEvents::RESPONSE, array($this, 'addDebugResponseMatch'));
            }
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function addDebugResponseMismatch(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->set('X-SignedRequest-Debug', 'false');
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function addDebugResponseMatch(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->set('X-SignedRequest-Debug', 'true');
    }

    /**
     * @param SigningServiceInterface $service
     */
    public function setSigningService(SigningServiceInterface $service)
    {
        $this->signingService = $service;
    }
}
