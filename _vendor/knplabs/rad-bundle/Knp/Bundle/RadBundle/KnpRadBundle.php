<?php

namespace Knp\Bundle\RadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Knp\Bundle\RadBundle\DependencyInjection\Compiler\AsseticTemplateResourcesPass;
use Knp\Bundle\RadBundle\DependencyInjection\Compiler\TranslationsPass;

class KnpRadBundle extends Bundle
{
	/**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AsseticTemplateResourcesPass);
        $container->addCompilerPass(new TranslationsPass);
    }
}
