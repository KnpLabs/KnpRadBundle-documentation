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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Patches internal Symfony2 services to be RAD.
 */
class InternalServicesPatchingPass implements CompilerPassInterface
{
    /**
     * Patches kernel parameters.
     *
     * @param ContainerBuilder $container Container instance
     */
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
        $container->setParameter('twig.form.resources', array_merge(
            $container->getParameter('twig.form.resources'),
            array('KnpRadBundle::form_bootstrap_layout.html.twig')
        ));

        $service = $container->findDefinition('file_locator');
        $service->replaceArgument(1, '%kernel.project_dir%');

        $service = $container->findDefinition('templating.finder');
        $service->replaceArgument(2, '%kernel.project_dir%');

        $service = $container->findDefinition('templating.helper.code');
        $service->replaceArgument(2, '%kernel.project_dir%');
    }
}
