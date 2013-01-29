<?php

namespace BR\SignedRequestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

use BR\SignedRequestBundle\Service\SigningServiceInterface;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class SignedResponseListener
{
    private $salt;
    private $signingService;

    public function __construct($salt)
    {
        $this->salt = $salt;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $hashed = $this->signingService->createResponseSignature($event->getResponse(), $this->salt);

        $event->getResponse()->headers->set('X-SignedRequest', $hashed);
    }

    public function setSigningService(SigningServiceInterface $service)
    {
        $this->signingService = $service;
    }
}
