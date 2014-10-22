SignedRequestBundle
===================

Symfony 2 bundle that provides request and response signing

[![Build Status](https://travis-ci.org/baldurrensch/SignedRequestBundle.png?branch=master)](https://travis-ci.org/baldurrensch/SignedRequestBundle)
[![Latest Stable Version](https://poser.pugx.org/br/signed-request-bundle/v/stable.png)](https://packagist.org/packages/br/signed-request-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e9fb2875-095a-43da-9f23-7c1d1a196f08/mini.png)](https://insight.sensiolabs.com/projects/e9fb2875-095a-43da-9f23-7c1d1a196f08)

## Introduction

This bundle provides very easy request signing (verification), and automatic response signing. This means that every request has to be signed with a hash of

    md5($requestUri . $content . $salt)

The response will be signed with:

    md5($responseContent . $salt)

The signatures are always put (and assumed) in a header called `X-SignedRequest`.

Contributions are as always welcome.

## Installation

Simply run assuming you have installed composer.phar or composer binary (or add to your `composer.json` and run composer install:

```bash
$ composer require br/signed-request-bundle
```

You can follow `dev-master`, or use a more stable tag (recommended for various reasons). On the [Github repository](https://github.com/baldurrensch/SignedRequestBundle), or on [Packagist](http://www.packagist.org), you can always find out the latest tag.

Now add the Bundle to your Kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new BR\SignedRequestBundle\BRSignedRequestBundle(),
        // ...
    );
}
```

## Configuration

To configure the bundle, edit your `config.yml`, or `config_{environment}.yml`:

```yml
# Signed Request Bundle
br_signed_request:
    salt: SALT_HERE
    debug: %kernel.debug%
    request_listener_enabled: true      # default
    response_listener_enabled: true     # default
    signature_mismatch:                 # optional
        status_code: 400
        response: Failed validation
```

If you put the listeners into `debug` mode, the request listener will always pass through the request, it will add a
`X-SignedRequest-Debug` header though, that will either contain "true" or "false" depending on whether the signature
was correct.

## Providing your own signing service

You can provide your own signing service by tagging your service as `br_signed_request.signing_service` and
implementing the `Service\SigningServiceInterface`. The bundle will then call the respective functions of your
service. You can take a look at the default service that is used (that just uses MD5) to see how it is setup.

## Using the signed request / response annotation

Instead of checking every request for a signature you can also add an annotation on a single controller function. In
order to use that you would have to set `request_listener_enabled` to `false`. The same is true for signing responses.
If you disable `response_listener_enabled`, you can use annotations to specify a controller action that should sign the
response. Of course, you can also combine both annotations.

### Example

```php
<?php

namespace Acme\YourBundle\Controller;

use BR\SignedRequestBundle\Annotations\SignedRequest;
use BR\SignedRequestBundle\Annotations\SignedResponse;

...

    /**
     * @SignedRequest
     */
    public function fooAction()
    {
        ...
    }

    /**
     * @SignedResponse
     */
    public function barAction()
    {
        ...
    }

    /**
     * @SignedRequest
     * @SignedResponse
     */
    public function bazAction()
    {
        ...
    }
...
```

## To Do & Future plans

None at the moment. Open an issue or submit a PR :)

