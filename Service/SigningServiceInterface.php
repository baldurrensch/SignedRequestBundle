<?php

namespace BR\SignedRequestBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
interface SigningServiceInterface
{
    /**
     * Creates the signature for the passed in request
     * @param  Request $request
     * @param  String  $salt
     * @return String
     */
    public function createRequestSignature(Request $request, $salt);

    /**
     * Creates the signature for the passed in response
     * @param  Response $response
     * @param  String   $salt
     * @return String
     */
    public function createResponseSignature(Response $response, $salt);
}
