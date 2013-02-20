<?php

namespace BR\SignedRequestBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 */
class ControllerTest extends TestCase
{
    public function testResponse()
    {
        $client = $this->createClient();
        $requestSignature = md5('/test' . 'testsalt');
        $client->request('GET', '/test', array(), array(), array('HTTP_X-SignedRequest' => $requestSignature));

        $response = $client->getResponse();

        $expectedSignature = md5('TestResponse' . 'testsalt');

        $this->assertEquals($expectedSignature, $response->headers->get('x-signedrequest'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('TestResponse', $response->getContent());
    }

    public function testRequestWithoutSignature()
    {
        $client = $this->createClient();
        $client->request('GET', '/test');

        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }

    public function testRequestWithWrongSignature()
    {
        $client = $this->createClient();
        $requestSignature = md5('/test' . 'testsaltWrong');
        $client->request('GET', '/test', array(), array(), array('HTTP_X-SignedRequest' => $requestSignature));

        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }

    public function testResponseListenerDisabled()
    {
        $client = $this->createClient(array('environment' => 'response_disabled'));
        $requestSignature = md5('/test' . 'testsalt');
        $client->request('GET', '/test', array(), array(), array('HTTP_X-SignedRequest' => $requestSignature));

        $response = $client->getResponse();

        $this->assertNull($response->headers->get('x-signedrequest'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('TestResponse', $response->getContent());
    }

    public function testRequestListenerDisabled()
    {
        $client = $this->createClient(array('environment' => 'request_disabled'));
        $client->request('GET', '/test');

        $response = $client->getResponse();

        $expectedSignature = md5('TestResponse' . 'testsalt');

        $this->assertEquals($expectedSignature, $response->headers->get('x-signedrequest'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('TestResponse', $response->getContent());
    }

    public function testBothListenerDisabled()
    {
        $client = $this->createClient(array('environment' => 'both_disabled'));
        $client->request('GET', '/test');

        $response = $client->getResponse();

        $this->assertNull($response->headers->get('x-signedrequest'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('TestResponse', $response->getContent());
    }
}
