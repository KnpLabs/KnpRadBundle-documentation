<?php

namespace Knp\Bundle\RadBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\AsseticBundle\DependencyInjection\DirectoryResourceDefinition;
use Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler\TemplateResourcesPass as BasePass;

class AsseticTemplateResourcesPass extends BasePass
{
    protected function setBundleDirectoryResources(ContainerBuilder $container, $engine, $bundleDirName, $bundleName)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $class   = $bundles[$bundleName];

        $r = new \ReflectionClass($class);
        if ($r->isSubclassOf('Knp\Bundle\RadBundle\Bundle\ApplicationBundle')) {
            $bundleDirName = sprintf('%s/%s',
                str_replace('\\', '/', $container->getParameter('kernel.project_dir'))
            );
        }

        $container->setDefinition(
            'assetic.'.$engine.'_directory_resource.'.$bundleName,
            new DirectoryResourceDefinition($bundleName, $engine, array(
                $bundleDirName.'/Resources/views',
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
