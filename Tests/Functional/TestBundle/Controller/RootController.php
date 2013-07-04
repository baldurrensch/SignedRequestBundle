<?php

namespace BR\SignedRequestBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use BR\SignedRequestBundle\Annotations\SignedRequest;
use BR\SignedRequestBundle\Annotations\SignedResponse;

class RootController extends Controller
{
    public function testAction(Request $request)
    {
        return new Response("TestResponse");
    }

    /**
     * @return Response
     * @SignedRequest
     */
    public function annotationRequestAction()
    {
        return new Response("TestResponse");
    }

    /**
     * @return Response
     * @SignedResponse
     */
    public function annotationResponseAction()
    {
        return new Response("TestResponse");
    }

    /**
     * @return Response
     * @SignedRequest
     * @SignedResponse
     */
    public function annotationBothAction()
    {
        return new Response("TestResponse");
    }
}
