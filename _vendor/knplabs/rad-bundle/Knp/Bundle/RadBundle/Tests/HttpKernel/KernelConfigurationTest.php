<?php

namespace Knp\Bundle\RadBundle\Tests\HttpKernel;

use Symfony\Component\Filesystem\Filesystem;
use Knp\Bundle\RadBundle\HttpKernel\KernelConfiguration;

require_once __DIR__.'/../../HttpKernel/RadAppKernel.php';

class KernelConfigurationTest extends \PHPUnit_Framework_TestCase
{
    private $fs;
    private $kernel;
    private $cacheDir;
    private $configDir;

    protected function setUp()
    {
        $this->fs = new Filesystem();

        $this->cacheDir  = sys_get_temp_dir().'/rad_bundle';
        $this->configDir = sys_get_temp_dir().'/rad_bundle';

        $this->fs->mkdir($this->cacheDir);
        $this->fs->mkdir($this->configDir);

        $this->kernel = $this->getMockBuilder('RadAppKernel')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->cacheDir);
        $this->fs->remove($this->configDir);
    }

    public function testNormalConfigReading()
    {
        $config = new KernelConfiguration('dev', $this->configDir, $this->cacheDir, true);

        file_put_contents($this->configDir.'/project.yml', $this->getConfigurationYaml());

        $config->load();

        $this->assertSame('Acme\Some\HelloApp', $config->getProjectName());
        $this->assertSame(array(
            'database_driver' => 'pdo_mysql',
            'locale'          => 'en',
        ), $config->getParameters());
        $this->assertSame(array('some/config1', 'some/config2'), $config->getConfigs());

        $this->kernel
            ->expects($this->once())
            ->method('getRootDir')
            ->will($this->returnValue($this->configDir));

        $bundles = $config->getBundles($this->kernel);

        $this->assertCount(4, $bundles);
        $this->assertInstanceOf('Knp\Bundle\RadBundle\Tests\HttpKernel\TestBundle', $bundles[0]);
        $this->assertInstanceOf('Knp\Bundle\RadBundle\Tests\HttpKernel\TestBundle2', $bundles[1]);
        $this->assertInstanceOf('Knp\Bundle\RadBundle\Tests\HttpKernel\TestBundle3', $bundles[2]);
        $this->assertInstanceOf('Knp\Bundle\RadBundle\Bundle\ApplicationBundle', $bundles[3]);
    }

    public function testProdConfigReading()
    {
        $config = new KernelConfiguration('prod', $this->configDir, $this->cacheDir, true);

        file_put_contents($this->configDir.'/project.yml', $this->getConfigurationYaml());

        $config->load();

        $this->assertSame('Acme\Some\HelloApp', $config->getProjectName());
        $this->assertSame(array(
            'database_driver' => 'pdo_mysql',
            'locale'          => 'en',
        ), $config->getParameters());
        $this->assertSame(array('some/config1', 'some/config3'), $config->getConfigs());

        $this->kernel
            ->expects($this->once())
            ->method('getRootDir')
            ->will($this->returnValue($this->configDir));

        $bundles = $config->getBundles($this->kernel);

        $this->assertCount(3, $bundles);
        $this->assertInstanceOf('Knp\Bundle\RadBundle\Tests\HttpKernel\TestBundle', $bundles[0]);
        $this->assertInstanceOf('Knp\Bundle\RadBundle\Tests\HttpKernel\TestBundle2', $bundles[1]);
        $this->assertInstanceOf('Knp\Bundle\RadBundle\Bundle\ApplicationBundle', $bundles[2]);
    }

    private function getConfigurationYaml()
    {
        return <<<YAML
name: Acme\Some\HelloApp

all:
    bundles:
        Knp\Bundle\RadBundle\Tests\HttpKernel\TestBundle:     ~
        Knp\Bundle\RadBundle\Tests\HttpKernel\TestBundle2:    ~

    configs:
        - some/config1

    parameters:
        database_driver:   pdo_mysql

        locale:            en

prod:
    configs:
        - some/config3

dev:
    configs: [ some/config2 ]
    bundles:
        Knp\Bundle\RadBundle\Tests\HttpKernel\TestBundle3:    ~
YAML;
    }
}

class TestBundle{}
class TestBundle2{}
class TestBundle3{}
