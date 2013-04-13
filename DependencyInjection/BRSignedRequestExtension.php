<?php

namespace BR\SignedRequestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class BRSignedRequestExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('br_signed_request.salt', $config['salt']);
        $container->setParameter('br_signed_request.signature_mismatch.status_code', $config['signature_mismatch']['status_code']);
        $container->setParameter('br_signed_request.signature_mismatch.response', $config['signature_mismatch']['response']);
        $container->setParameter('br_signed_request.request_listener.enabled', $config['request_listener_enabled']);
        $container->setParameter('br_signed_request.response_listener.enabled', $config['response_listener_enabled']);
        $container->setParameter('br_signed_request.debug', $config['debug']);
    }
}
