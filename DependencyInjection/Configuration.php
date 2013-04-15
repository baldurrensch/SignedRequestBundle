<?php

namespace BR\SignedRequestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('br_signed_request');
        $rootNode->children()
            ->scalarNode('salt')
                ->isRequired()
                ->cannotBeEmpty()
                ->info('The salt to use for signing')
                ->example('sdfjkhsdjkfhsdjkfhnsjkfhw8904239uew9043')
            ->end()
            ->booleanNode('request_listener_enabled')
                ->info('Whether the request listener should check the signature')
                ->defaultTrue()
            ->end()
            ->booleanNode('response_listener_enabled')
                ->info('Whether the response listener should check the signature')
                ->defaultTrue()
            ->end()
            ->booleanNode('debug')
                ->defaultFalse()
                ->info('Whether to turn on debug mode')
            ->end()
            ->arrayNode('signature_mismatch')
                ->addDefaultsIfNotSet()
                ->children()
                ->scalarNode('status_code')
                    ->defaultValue(404)
                    ->info('The status code to return if the signature is incorrect')
                    ->example('404')
                ->end()
                ->scalarNode('response')
                    ->defaultValue("")
                    ->info('The error message to put in the response when the signature is incorrect')
                    ->example('Signature mismatch')
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
