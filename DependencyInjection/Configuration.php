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
            ->end()
            ->arrayNode('signature_mismatch')->children()
                ->scalarNode('status_code')
                    ->defaultValue(404)
                ->end()
                ->scalarNode('response')
                    ->defaultValue("")
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
