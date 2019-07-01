<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\DependencyInjection;

use Hessnatur\SimpleRestCRUDBundle\Manager\ApiResourceManager;
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
                            ->scalarNode('api_prefix')->defaultValue('')->end()
                            ->scalarNode('api_resource_manager')->defaultValue(ApiResourceManager::class)->end()
                        ->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
