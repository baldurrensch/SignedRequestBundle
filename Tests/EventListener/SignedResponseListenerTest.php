<?php

namespace BR\SignedRequestBundle\Tests\EventListener;

use BR\SignedRequestBundle\EventListener\SignedResponseListener;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent as Event;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use BR\SignedRequestBundle\Service\MD5SigningService as SigningService;

class SignedResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    const GOOD_HASH = '3daf6f1b5c482e2c874ffbf8b440f670';
    private $salt = 'abc';

    public function testSubrequestReturns()
    {
        $this->event->expects($this->once())->method('getRequestType')
            ->will($this->returnValue(HttpKernel::SUB_REQUEST));

        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->expects($this->never())->method('getRequestUri');

        $this->event->expects($this->never())->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->listener = new SignedResponseListener($this->salt);
        $this->listener->setSigningService(new SigningService());
        $this->listener->onKernelResponse($this->event);
    }

    public function testResponseContainsHeader()
    {
        $this->event->expects($this->once())->method('getRequestType')
            ->will($this->returnValue(HttpKernel::MASTER_REQUEST));

        $this->response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $this->response->expects($this->once())->method('getContent')
            ->will($this->returnValue('content'));

        $this->headers = $this->getMockBuilder('Symfony\Component\HttpFoundation\HeaderBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->headers->expects($this->once())->method('set')
            ->with('X-SignedRequest', self::GOOD_HASH);

        $this->response->headers = $this->headers;

        $this->event->expects($this->any())->method('getResponse')
            ->will($this->returnValue($this->response));

        $this->listener = new SignedResponseListener($this->salt);
        $this->listener->setSigningService(new SigningService());
        $this->listener->onKernelResponse($this->event);
    }

    protected function setup()
    {
         $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
