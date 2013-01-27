SignedRequestBundle
===================

Symfony 2 bundle that provides request and response signing

[![Build Status](https://travis-ci.org/baldurrensch/SignedRequestBundle.png?branch=master)](https://travis-ci.org/baldurrensch/SignedRequestBundle)

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
    request_listener_enabled: true      # default
    response_listener_enabled: true     # default
    signature_mismatch:                 # optional
        status_code: 400
        response: Failed validation
```

## To Do & Future plans
- Ability to put the request listener in "verify" mode. Just check whether teh signature is correct and add another header then.
- Provide the ability to tie in a custom signing service that will be used (This would then allow dynamic keys)!
