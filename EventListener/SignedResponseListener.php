<?php

namespace BR\SignedRequestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class SignedResponseListener
{
	private $salt;

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

        $responseContent = $event->getResponse()->getContent();

        $hashed = md5($responseContent . $this->salt);
        // $hashFromRequest = $event->getRequest()->headers->get('X-SignedRequest');

        $event->getResponse()->headers->set('X-SignedRequest', $hashed);
    }
}