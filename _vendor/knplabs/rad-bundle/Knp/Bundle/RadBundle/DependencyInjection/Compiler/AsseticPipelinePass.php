<?php

/*
 * This file is part of the KnpRadBundle package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Enables assetic pipeline support.
 */
class AsseticPipelinePass implements CompilerPassInterface
{
    /**
     * Adds asset references to the asset manager.
     *
     * @param ContainerBuilder $container Container instance
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.asset_factory')) {
            return;
        }
        $locator = new Definition(
            'Knp\\Bundle\\RadBundle\\Assetic\\PipelineAssetLocator', array(
                array(
                    $container->getParameter('kernel.project_dir').'/assets',
                    $container->getParameter('kernel.root_dir').'/vendor/assets',
                ),
            )
        );

        $factory = $container->getDefinition('assetic.asset_factory');
        $factory->setClass('Knp\\Bundle\\RadBundle\\Assetic\\PipelineAssetFactory');
        $factory->addMethodCall('setPipelineAssetLocator', array($locator));
    }
}
