<?php

declare(strict_types=1);

namespace AppTest\Content\Markdown;

use App\Content\Markdown\MarkdownConverterFactory;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\MarkdownConverter;
use Psr\Container\ContainerInterface;
use Ramsey\Test\Website\TestCase;

class MarkdownConverterFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $environment = $this->mockery(EnvironmentInterface::class);

        $container = $this->mockery(ContainerInterface::class);
        $container->expects()->get(EnvironmentInterface::class)->andReturn($environment);

        $factory = new MarkdownConverterFactory();

        /** @var MarkdownConverter $converter */
        $converter = $factory($container);

        $this->assertSame($environment, $converter->getEnvironment());
    }
}
