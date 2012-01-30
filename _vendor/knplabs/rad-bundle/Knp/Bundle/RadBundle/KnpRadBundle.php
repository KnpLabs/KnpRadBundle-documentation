<?php

namespace Knp\Bundle\RadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Knp\Bundle\RadBundle\DependencyInjection\Compiler\RegisterDoctrineMappingDriverPass;

class KnpRadBundle extends Bundle
{
	/**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterDoctrineMappingDriverPass());
    }
}
