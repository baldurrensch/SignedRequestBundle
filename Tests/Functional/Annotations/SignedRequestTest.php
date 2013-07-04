<?php

namespace BR\SignedRequestBundle\Tests\Functional\Annotations;

use BR\SignedRequestBundle\Tests\Functional\TestCase;

/**
 * @group functional
 *
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class SignedRequestTest extends TestCase
{
    public function getTestCases()
    {
        return array(
            array('9396ad0fbdec9d945f13c9711c249569', 'both_disabled', 200, 'TestResponse', null),
            array('9396ad0fbdec9d945f13c9711c24956', 'both_disabled', 404, '', null),
            array('9396ad0fbdec9d945f13c9711c249569', 'both_disabled_debug', 200, 'TestResponse', 'true'),
            array('9396ad0fbdec9d945f13c9711c24956', 'both_disabled_debug', 200, 'TestResponse', 'false'),
        );
    }

    /**
     * @param string      $signature
     * @param string      $environment
     * @param int         $expectedStatusCode
     * @param string      $expectedContent
     * @param string|null $debugResponse
     * @dataProvider getTestCases
     */
    public function testSignedRequestAnnotation($signature, $environment, $expectedStatusCode, $expectedContent, $debugResponse)
    {
        $client = $this->createClient(array('environment' => $environment));
        $client->request('GET', '/annotation-request', array(), array(), array('HTTP_X-SignedRequest' => $signature));

        $this->assertEquals($expectedStatusCode, $client->getResponse()->getStatusCode());
        $this->assertEquals($expectedContent, $client->getResponse()->getContent());
        if (is_null($debugResponse)) {
            $this->assertNull($client->getResponse()->headers->get('x-signedrequest-debug'));
        } else {
            $this->assertEquals($debugResponse, $client->getResponse()->headers->get('x-signedrequest-debug'));
        }
    }
}
