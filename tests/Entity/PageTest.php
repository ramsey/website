<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Metadata;
use App\Entity\Page;
use App\Tests\TestCase;

class PageTest extends TestCase
{
    public function testDefaultProperties(): void
    {
        $page = new Page(
            title: 'A Page',
            content: 'Content of a page.',
        );

        $this->assertSame('A Page', $page->title);
        $this->assertSame('Content of a page.', $page->content);
        $this->assertInstanceOf(Metadata::class, $page->metadata);
        $this->assertCount(0, $page->metadata);
    }

    public function testMetadata(): void
    {
        $metadata = new Metadata([
            'foo' => 'bar',
        ]);

        $page = new Page(
            'A Page',
            'Content of a page.',
            metadata: $metadata,
        );

        $this->assertSame($metadata, $page->metadata);
    }
}
