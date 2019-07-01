<?php

namespace Hessnatur\SimpleRestCRUDBundle\DependencyInjection;

use Hessnatur\SimpleRestCRUDBundle\Manager\ApiResourceManager;
use Hessnatur\SimpleRestCRUDBundle\Manager\ApiResourceManagerInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
 *
 * @author Felix Niedballa <felix.niedballa@hess-natur.de>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('hessnatur_simple_rest_crud');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('settings')
                    ->addDefaultsIfNotSet()
                        ->children()
                        ->scalarNode('apiResourceManager')->defaultValue(ApiResourceManager::class)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
