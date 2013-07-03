<?php

namespace BR\SignedRequestBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use BR\SignedRequestBundle\Annotations\SignedRequest;

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
    public function annotationAction()
    {
        return new Response("TestResponse");
    }
}
