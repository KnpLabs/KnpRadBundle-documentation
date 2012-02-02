<?php

namespace Knp\Bundle\RadBundle\Tests\Bundle;

use Knp\Bundle\RadBundle\Bundle\ApplicationBundle;

class ApplicationBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testCreationWithLongProjectNamespace()
    {
        $bundle = new ApplicationBundle('Some\\Enormously\\Big\\Project\\Namespace', 'src');

        $this->assertSame('Some\\Enormously\\Big\\Project\\Namespace', $bundle->getNamespace());
        $this->assertSame('Namespace', $bundle->getName());
        $this->assertSame('src/Some/Enormously/Big/Project/Namespace', $bundle->getPath());
    }

    public function testCreationWithShortProjectNamespace()
    {
        $bundle = new ApplicationBundle('Namespace', 'src');

        $this->assertSame('Namespace', $bundle->getNamespace());
        $this->assertSame('Namespace', $bundle->getName());
        $this->assertSame('src/Namespace', $bundle->getPath());
    }

    public function testCreationWithNormalProjectNamespace()
    {
        $bundle = new ApplicationBundle('Acme\\Hello', 'src');

        $this->assertSame('Acme\\Hello', $bundle->getNamespace());
        $this->assertSame('Hello', $bundle->getName());
        $this->assertSame('src/Acme/Hello', $bundle->getPath());
    }

    public function testGetGenericApplicationExtension()
    {
        $bundle = new ApplicationBundle('Knp\Bundle\RadBundle\ApplicationBundle\Fixture1\App', '');

        $this->assertInstanceOf(
            'Knp\Bundle\RadBundle\Extension\ApplicationExtension', $bundle->getContainerExtension()
        );
    }

    public function testGetCustomApplicationExtension()
    {
        $bundle = new ApplicationBundle('Knp\Bundle\RadBundle\ApplicationBundle\Fixture2\App', '');

        $this->assertInstanceOf(
            'Knp\Bundle\RadBundle\ApplicationBundle\Fixture2\App\DependencyInjection\AppExtension',
            $bundle->getContainerExtension()
        );
    }
}

namespace Knp\Bundle\RadBundle\ApplicationBundle\Fixture2\App\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    }
}

