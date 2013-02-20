<?php

namespace BR\SignedRequestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Response;
use BR\SignedRequestBundle\Service\SigningServiceInterface;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class SignedRequestListener
{
    private $salt;
    private $statusCode;
    private $response;
    private $signingService;

    public function __construct($salt, $statusCode, $response)
    {
        $this->salt       = $salt;
        $this->statusCode = $statusCode;
        $this->response   = $response;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $hashed = $this->signingService->createRequestSignature($event->getRequest(), $this->salt);
        $hashFromRequest = $event->getRequest()->headers->get('X-SignedRequest');

        if ($hashed != $hashFromRequest) {
            $event->setResponse(new Response($this->response, $this->statusCode));
        }
    }

    public function setSigningService(SigningServiceInterface $service)
    {
        $this->signingService = $service;
    }
}
