<?php

namespace BR\SignedRequestBundle\Tests\EventListener;

use BR\SignedRequestBundle\EventListener\SignedRequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent as Event;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BR\SignedRequestBundle\Service\MD5SigningService as SigningService;

class SignedRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    const GOOD_HASH = '4285efb8202976e28bc8bae1b4715c00';
    const BAD_HASH  = '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    private $salt = 'abc';
    private $statusCode = 400;
    private $response = 'Major fail';

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

        $this->listener = new SignedRequestListener($this->salt, $this->statusCode, $this->response);
        $this->listener->setSigningService(new SigningService());
        $this->listener->onKernelRequest($this->event);
    }

    public function testCorrectHash()
    {
        $this->setupEvent(self::GOOD_HASH);

        $this->event->expects($this->never())->method('setResponse');

        $this->listener = new SignedRequestListener($this->salt, $this->statusCode, $this->response);
        $this->listener->setSigningService(new SigningService());
        $this->listener->onKernelRequest($this->event);
    }

    public function testIncorrectHash()
    {
        $this->setupEvent(self::BAD_HASH);

        $failResponse = new Response($this->response, $this->statusCode);

        $this->event->expects($this->once())->method('setResponse')
            ->with($failResponse);

        $this->listener = new SignedRequestListener($this->salt, $this->statusCode, $this->response);
        $this->listener->setSigningService(new SigningService());
        $this->listener->onKernelRequest($this->event);
    }

    public function testMissingHeader()
    {
        $this->setupEvent(null);

        $failResponse = new Response($this->response, $this->statusCode);

        $this->event->expects($this->once())->method('setResponse')
            ->with($failResponse);

        $this->listener = new SignedRequestListener($this->salt, $this->statusCode, $this->response);
        $this->listener->setSigningService(new SigningService());
        $this->listener->onKernelRequest($this->event);
    }

    protected function setupEvent($headerValue)
    {
        $this->event->expects($this->once())->method('getRequestType')
            ->will($this->returnValue(HttpKernel::MASTER_REQUEST));

        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->expects($this->once())->method('getRequestUri')
            ->will($this->returnValue('uri'));

        $this->request->expects($this->once())->method('getContent')
            ->will($this->returnValue('content'));

        $this->headers = $this->getMockBuilder('Symfony\Component\HttpFoundation\HeaderBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->headers->expects($this->once())->method('get')
            ->with('X-SignedRequest')
            ->will($this->returnValue($headerValue));

        $this->request->headers = $this->headers;

        $this->event->expects($this->any())->method('getRequest')
            ->will($this->returnValue($this->request));
    }

    protected function setup()
    {
         $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
