<?php

declare(strict_types=1);

namespace AppTest;

use App\ConfigProvider;
use Ramsey\Test\Website\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testInvokeConfigProvider(): void
    {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        $this->assertArrayHasKey('dependencies', $config);
        $this->assertArrayHasKey('templates', $config);
    }

    public function testGetDependencies(): void
    {
        $configProvider = new ConfigProvider();
        $dependencies = $configProvider->getDependencies();

        $this->assertArrayHasKey('invokables', $dependencies);
        $this->assertArrayHasKey('factories', $dependencies);
    }

    public function testGetTemplates(): void
    {
        $configProvider = new ConfigProvider();
        $templates = $configProvider->getTemplates();

        $this->assertArrayHasKey('paths', $templates);
    }
}
