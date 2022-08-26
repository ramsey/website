<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\CommonMark;
use App\Tests\TestCase;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

class CommonMarkTest extends TestCase
{
    public function testWithoutFrontMatter(): void
    {
        $content = '**Foo**';

        $commonMark = new CommonMark();
        $renderedContent = $commonMark->convert($content);

        $this->assertNotInstanceOf(RenderedContentWithFrontMatter::class, $renderedContent);
    }

    public function testWithFrontMatter(): void
    {
        $content = <<<'EOD'
            ---
            foo: bar
            ---
            **Foo**
            EOD;

        $commonMark = new CommonMark();
        $renderedContent = $commonMark->convert($content);

        $this->assertInstanceOf(RenderedContentWithFrontMatter::class, $renderedContent);
    }

    public function testConvertsMarkdownToHtml(): void
    {
        $content = '**Foo**';
        $expected = "<p><strong>Foo</strong></p>\n";

        $commonMark = new CommonMark();
        $renderedContent = $commonMark->convert($content);

        $this->assertSame($expected, $renderedContent->getContent());
    }

    public function testWithConfiguration(): void
    {
        $content = '**Foo**';
        $expected = "<p>**Foo**</p>\n";

        $commonMark = new CommonMark(['commonmark' => ['enable_strong' => false]]);
        $renderedContent = $commonMark->convert($content);

        $this->assertSame($expected, $renderedContent->getContent());
    }
}
