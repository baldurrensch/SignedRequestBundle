<?php

namespace BR\SignedRequestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class SignedRequestListener
{
    private $salt;
    private $statusCode;
    private $response;

    public function __construct($salt, $statusCode, $response)
    {
        $this->salt       = $salt;
        $this->statusCode = $statusCode;
        $this->response    = $response;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $requestUri = $event->getRequest()->getRequestUri();
        $content    = $event->getRequest()->getContent();

        $hashed = md5($requestUri . $content . $this->salt);
        $hashFromRequest = $event->getRequest()->headers->get('X-SignedRequest');

        if ($hashed != $hashFromRequest) {
            $event->setResponse(new Response($this->response, $this->statusCode));
        }
    }
}
