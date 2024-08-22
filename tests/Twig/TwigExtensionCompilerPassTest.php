<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\Extension;
use App\Twig\TwigExtensionCompilerPass;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Twig\TwigFunction;

use function count;

#[TestDox('TwigExtensionCompilerPass')]
class TwigExtensionCompilerPassTest extends KernelTestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox('registers custom Twig functions, filters, etc. during kernel boot')]
    public function testCompilePassRunsDuringKernelBoot(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        /** @var Extension $extension */
        $extension = $container->get(Extension::class);

        $functions = [];
        foreach ($extension->getFunctions() as $function) {
            $functions[] = $function;
        }

        $this->assertGreaterThan(0, count($functions));
        $this->assertContainsOnlyInstancesOf(TwigFunction::class, $functions);
    }

    #[TestDox('does nothing if App\\Twig\\Extension is not registered')]
    public function testWhenExtensionNotRegistered(): void
    {
        $containerBuilder = Mockery::mock(ContainerBuilder::class);
        $containerBuilder->expects('has')->with(Extension::class)->andReturn(false);
        $containerBuilder->expects('findDefinition')->never();
        $containerBuilder->expects('findTaggedServiceIds')->never();

        $pass = new TwigExtensionCompilerPass();
        $pass->process($containerBuilder);
    }
}
