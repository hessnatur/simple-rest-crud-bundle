<?php

namespace Hessnatur\SimpleRestCRUDBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        return new TreeBuilder('hessnatur_simple_rest_crud');
    }
}
