<?php

namespace Knp\Bundle\RadBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\AsseticBundle\DependencyInjection\DirectoryResourceDefinition;
use Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler\TemplateResourcesPass as BasePass;

class AsseticTemplateResourcesPass extends BasePass
{
    protected function setBundleDirectoryResources(ContainerBuilder $container, $engine, $bundleDirName, $bundleName)
    {
        $container->setDefinition(
            'assetic.'.$engine.'_directory_resource.'.$bundleName,
            new DirectoryResourceDefinition($bundleName, $engine, array(
                $bundleDirName.'/views',
            ))
        );
    }

    protected function setAppDirectoryResources(ContainerBuilder $container, $engine)
    {
        $container->setDefinition(
            'assetic.'.$engine.'_directory_resource.kernel',
            new DirectoryResourceDefinition('', $engine, array(
                $container->getParameter('kernel.project_dir').'/views'
            ))
        );
    }
}
