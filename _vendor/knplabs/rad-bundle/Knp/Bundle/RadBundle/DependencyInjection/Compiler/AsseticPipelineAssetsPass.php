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

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds assets from applications assets folder to pipeline.
 */
class AsseticPipelineAssetsPass implements CompilerPassInterface
{
    /**
     * Adds asset references to the asset manager.
     *
     * @param ContainerBuilder $container Container instance
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.asset_manager')) {
            return;
        }
        $container->setParameter('assetic.asset_manager.class',
            'Knp\\Bundle\\RadBundle\\Assetic\\PipelinedAssetManager'
        );

        $includes = array(
            $container->getParameter('kernel.project_dir').'/assets',
            $container->getParameter('kernel.root_dir').'/vendor/assets',
        );

        foreach ($includes as $path) {
            // javascripts
            if (is_dir($path.'/js')) {
                $javascripts = Finder::create()->files()->name('*.js')->in($path.'/js');
                foreach ($javascripts as $javascriptFile) {
                    $this->addAsset($container, $javascriptFile, 'js');
                }
            }

            // stylesheets
            if (is_dir($path.'/css')) {
                $stylesheets = Finder::create()->files()->name('*.css')->in($path.'/css');
                foreach ($stylesheets as $stylesheetFile) {
                    $this->addAsset($container, $stylesheetFile, 'css');
                }
            }
        }
    }

    protected function addAsset(ContainerBuilder $container, SplFileInfo $file, $type)
    {
        $scriptPath = $file->getRelativePathname();
        $nameParts  = explode('.', basename($scriptPath));

        $filters   = array();
        $extension = array_pop($nameParts);
        if (1 < count($nameParts)) {
            $filterName = array_pop($nameParts);

            if ($container->hasDefinition($filter = 'assetic.filter.'.$filterName)) {
                $filters[] = new Reference($filter);
            } else {
                $nameParts[] = $filterName;
            }
        }

        $assetName  = '_pipeline_'.preg_replace(
            array('/\//', '/[^a-zA-Z0-9_]/'), array('_', ''),
            ($file->getRelativePath() ? $file->getRelativePath().'/' : '').implode('.', $nameParts)
        ).'_'.$type;

        $definition = new Definition('Assetic\\Asset\\FileAsset', array(
            $file->getPathname(), $filters
        ));
        $definition->addMethodCall('setTargetPath', array($type.'/'.$scriptPath));

        $am = $container->getDefinition('assetic.asset_manager');
        $am->addMethodCall('set', array($assetName, $definition));
    }
}
