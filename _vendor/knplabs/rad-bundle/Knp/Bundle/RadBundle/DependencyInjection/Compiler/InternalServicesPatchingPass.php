<?php

namespace Knp\Bundle\RadBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Finder\Finder;

class InternalServicesPatchingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('router.options.generator_base_class',
            'Knp\Bundle\RadBundle\Routing\Generator\UrlGenerator'
        );
        $container->setParameter('templating.name_parser.class',
            'Knp\Bundle\RadBundle\Templating\TemplateNameParser'
        );
        $container->setParameter('controller_name_converter.class',
            'Knp\Bundle\RadBundle\Controller\ControllerNameParser'
        );
        $container->setParameter('controller_resolver.class',
            'Knp\Bundle\RadBundle\Controller\ControllerResolver'
        );

        $service = $container->findDefinition('file_locator');
        $service->replaceArgument(1, '%kernel.project_dir%');

        $service = $container->findDefinition('templating.finder');
        $service->replaceArgument(2, '%kernel.project_dir%');

        $service = $container->findDefinition('templating.helper.code');
        $service->replaceArgument(2, '%kernel.project_dir%');
    }
}
