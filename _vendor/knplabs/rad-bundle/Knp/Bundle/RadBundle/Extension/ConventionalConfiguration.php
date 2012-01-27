<?php

namespace Knp\Bundle\RadBundle\Extension;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConventionalConfiguration implements ConfigurationInterface
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->name);

        $rootNode
            ->fixXmlConfig('parameter')
            ->children()
                ->arrayNode('parameters')
                    ->useAttributeAsKey(0)
                    ->prototype('variable')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
