<?php

namespace BR\SignedRequestBundle\Tests\Functional\Annotations;

use BR\SignedRequestBundle\Tests\Functional\TestCase;

/**
 * @group functional
 *
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class SignedResponseTest extends TestCase
{
    public function testSignedResponseAnnotation()
    {
        $client = $this->createClient(array('environment' => 'both_disabled_debug'));
        $client->request('GET', '/annotation-response');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('TestResponse', $client->getResponse()->getContent());
        $this->assertNull($client->getResponse()->headers->get('x-signedrequest-debug'));
        $this->assertEquals('c980a05698a6ceacd0df2c8e12e34887', $client->getResponse()->headers->get('X-SignedRequest'));
    }
}
