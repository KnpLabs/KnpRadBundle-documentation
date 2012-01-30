<?php

namespace Knp\Bundle\RadBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Finder\Finder;

class FileLocatorsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $locator = $container->findDefinition('file_locator');
        $locator->replaceArgument(1, '%kernel.project_dir%');

        $finder = $container->findDefinition('templating.finder');
        $finder->replaceArgument(2, '%kernel.project_dir%');

        $codeHelper = $container->findDefinition('templating.helper.code');
        $codeHelper->replaceArgument(2, '%kernel.project_dir%');
    }
}
