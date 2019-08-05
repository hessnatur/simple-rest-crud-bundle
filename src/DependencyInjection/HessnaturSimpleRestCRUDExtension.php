<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\DependencyInjection;

use Hessnatur\SimpleRestCRUDBundle\Manager\ApiResourceManager;
use Hessnatur\SimpleRestCRUDBundle\Manager\ApiResourceManagerInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
class HessnaturSimpleRestCRUDExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('hessnatur_simple_rest_crud.api_prefix', $config['settings']['api_prefix']);
        $container->setParameter('hessnatur_simple_rest_crud.extend_with_filter', $config['settings']['extend_with_filter']);
        $container->setParameter('hessnatur_simple_rest_crud.extend_with_query', $config['settings']['extend_with_query']);

        $this->registerServices($config, $container);
    }

    private function registerServices($config, ContainerBuilder $containerBuilder)
    {
        if($config['settings']['api_resource_manager'] !== ApiResourceManagerInterface::class) {
            $alias = 'hessnatur_simple_rest_crud.api_resource_manager';
            $containerBuilder->setAlias($alias, $config['settings']['api_resource_manager']);
            $containerBuilder->setAlias(ApiResourceManagerInterface::class, $alias);
        }
    }
}
