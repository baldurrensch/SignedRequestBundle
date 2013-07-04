<?php

namespace BR\SignedRequestBundle\Tests\Functional\Annotations;

use BR\SignedRequestBundle\Tests\Functional\TestCase;

/**
 * @group functional
 *
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class CombinedAnnotationTest extends TestCase
{
    public function testBothAnnotationMatch()
    {
        $client = $this->createClient(array('environment' => 'both_disabled'));
        $client->request('GET', '/annotation-both', array(), array(), array('HTTP_X-SignedRequest' => 'e8e1d3489a49fbf38612de03c99bccca'));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('TestResponse', $client->getResponse()->getContent());
        $this->assertNull($client->getResponse()->headers->get('x-signedrequest-debug'));
        $this->assertEquals('c980a05698a6ceacd0df2c8e12e34887', $client->getResponse()->headers->get('X-SignedRequest'));
    }

    public function testBothAnnotationMismatch()
    {
        $client = $this->createClient(array('environment' => 'both_disabled'));
        $client->request('GET', '/annotation-both', array(), array(), array('HTTP_X-SignedRequest' => 'e8e1d3489a49fbf38612de03c99bccc'));

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals('', $client->getResponse()->getContent());
        $this->assertNull($client->getResponse()->headers->get('x-signedrequest-debug'));
        $this->assertEquals('315240c61218a4a861ec949166a85ef0', $client->getResponse()->headers->get('X-SignedRequest'));
    }
}
