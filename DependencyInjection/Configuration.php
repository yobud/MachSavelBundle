<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('mach_savel');

        $rootNode
            ->children()
                ->scalarNode('default_redirect')->isRequired()->end()
                ->scalarNode('security_provider')->defaultValue('main')->end()
                ->arrayNode('services')->isRequired()->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('snufkin')->isRequired()->end()
                            ->scalarNode('client_id')->isRequired()->end()
                            ->scalarNode('client_secret')->isRequired()->end()
                            ->arrayNode('callback')
                                ->children()
                                    ->scalarNode('route')->isRequired()->end()
                                    ->variableNode('params')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                            ->arrayNode('binding')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('property')->isRequired()->end()
                                        ->scalarNode('endpoint')->defaultValue('default')->end()
                                        ->scalarNode('path')->isRequired()->end()
                                        ->booleanNode('isDiscriminator')->defaultValue(false)->end()
                                        ->booleanNode('isCreator')->defaultValue(false)->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('scope')
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('bridge_service')->isRequired()->end()
                            ->scalarNode('user_class')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
}
