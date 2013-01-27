<?php

namespace BR\SignedRequestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use BR\SignedRequestBundle\DependencyInjection\Compiler\ConfigurationCheckPass;

class BRSignedRequestBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationCheckPass());
    }
}
