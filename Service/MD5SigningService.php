<?php

namespace BR\SignedRequestBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class MD5SigningService implements SigningServiceInterface
{
    public function createRequestSignature(Request $request, $salt)
    {
        $requestUri = $request->getRequestUri();
        $content    = $request->getContent();

        return md5($requestUri . $content . $salt);
    }

    public function createResponseSignature(Response $response, $salt)
    {
        $responseContent = $response->getContent();

        return md5($responseContent . $salt);
    }
}
