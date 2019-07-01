<?php

namespace Hessnatur\SimpleRestCRUDBundle\DependencyInjection;

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

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('hessnatur_simple_rest_crud.api_prefix', $config['settings']['api_prefix']);

        $this->registerServices($config, $container);
    }

    private function registerServices($config, ContainerBuilder $containerBuilder)
    {
        $services = [
            'api_resource_manager' => ApiResourceManagerInterface::class
        ];

        foreach ($services as $serviceID => $serviceClass) {
            $alias = 'hessnatur_simple_rest_crud.' . $serviceID;
            $containerBuilder->setAlias($alias, $config['settings'][$serviceID]);
            $containerBuilder->setAlias($serviceClass, $alias);
        }
    }
}
