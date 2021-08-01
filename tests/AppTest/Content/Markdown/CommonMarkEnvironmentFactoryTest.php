<?php

declare(strict_types=1);

namespace AppTest\Content\Markdown;

use App\Content\Markdown\CommonMarkEnvironmentFactory;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use Psr\Container\ContainerInterface;
use Ramsey\Test\Website\TestCase;

use function count;
use function in_array;

class CommonMarkEnvironmentFactoryTest extends TestCase
{
    public function testEnvironmentExtensionsRegistered(): void
    {
        $expectedExtensions = [
            AttributesExtension::class,
            AutolinkExtension::class,
            CommonMarkCoreExtension::class,
            DescriptionListExtension::class,
            FootnoteExtension::class,
            FrontMatterExtension::class,
            SmartPunctExtension::class,
            StrikethroughExtension::class,
            TableExtension::class,
        ];

        $factory = new CommonMarkEnvironmentFactory();

        $container = $this->mockery(ContainerInterface::class);
        $container->expects()->get('config')->andReturn([]);

        $environment = $factory($container);

        $extensions = $environment->getExtensions();

        $this->assertCount(count($expectedExtensions), $extensions);

        foreach ($extensions as $extension) {
            if (!in_array($extension::class, $expectedExtensions)) {
                $this->fail('Found unexpected CommonMark extension: ' . $extension::class);
            }
        }
    }

    public function testEnvironmentSetsConfiguration(): void
    {
        $factory = new CommonMarkEnvironmentFactory();

        $container = $this->mockery(ContainerInterface::class);
        $container->expects()->get('config')->andReturn([
            'commonmark' => [
                'allow_unsafe_links' => false,
            ],
        ]);

        $environment = $factory($container);

        $this->assertFalse($environment->getConfiguration()->get('allow_unsafe_links'));
    }
}
