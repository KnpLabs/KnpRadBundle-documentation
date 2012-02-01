<?php

/*
 * This file is part of the KnpRadBundle package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler\AssetManagerPass;

use Knp\Bundle\RadBundle\DependencyInjection\Compiler\TranslationsPass;
use Knp\Bundle\RadBundle\DependencyInjection\Compiler\FileLocatorsPass;
use Knp\Bundle\RadBundle\DependencyInjection\Compiler\AsseticTemplateResourcesPass;
use Knp\Bundle\RadBundle\DependencyInjection\Compiler\RegisterDoctrineMappingDriverPass;

/**
 * RadBundle for Symfony2.
 */
class KnpRadBundle extends Bundle
{
	/**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TranslationsPass);
        $container->addCompilerPass(new FileLocatorsPass);
        $container->addCompilerPass(new AsseticTemplateResourcesPass);
        $container->addCompilerPass(new AssetManagerPass);
        $container->addCompilerPass(new RegisterDoctrineMappingDriverPass);
    }
}
