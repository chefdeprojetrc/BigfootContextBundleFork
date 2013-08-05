<?php

namespace Bigfoot\Bundle\ContextBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bigfoot_context');

        $rootNode
            ->children()
                ->arrayNode('contexts')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('loaders')
                                ->useAttributeAsKey('name')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('values')
                                ->useAttributeAsKey('name')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('label')->isRequired()->end()
                                        ->scalarNode('value')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('default_value')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
