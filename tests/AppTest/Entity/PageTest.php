<?php

declare(strict_types=1);

namespace AppTest\Entity;

use App\Entity\Attributes;
use App\Entity\Page;
use Ramsey\Test\Website\TestCase;

class PageTest extends TestCase
{
    public function testGetTitle(): void
    {
        $title = $this->faker()->sentence;

        /** @var string $content */
        $content = $this->faker()->paragraphs(3, true);

        $page = new Page($title, $content, new Attributes([]));

        $this->assertSame($title, $page->getTitle());
    }

    public function testGetContent(): void
    {
        $title = $this->faker()->sentence;

        /** @var string $content */
        $content = $this->faker()->paragraphs(3, true);

        $page = new Page($title, $content, new Attributes([]));

        $this->assertSame($content, $page->getContent());
    }

    public function testGetAttributes(): void
    {
        $title = $this->faker()->sentence;

        /** @var string $content */
        $content = $this->faker()->paragraphs(3, true);

        $attributes = new Attributes([]);

        $page = new Page($title, $content, $attributes);

        $this->assertSame($attributes, $page->getAttributes());
    }
}
