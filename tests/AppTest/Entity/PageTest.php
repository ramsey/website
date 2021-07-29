<?php

declare(strict_types=1);

namespace AppTest\Entity;

use App\Entity\Attributes;
use App\Entity\Page;
use Ramsey\Test\Website\TestCase;

class PageTest extends TestCase
{
    private string $title;
    private string $content;

    protected function setUp(): void
    {
        $this->title = $this->faker()->sentence;

        /** @var string $content */
        $content = $this->faker()->paragraphs(3, true);
        $this->content = $content;
    }

    public function testGetTitle(): void
    {
        $page = new Page($this->title, $this->content);

        $this->assertSame($this->title, $page->getTitle());
    }

    public function testGetContent(): void
    {
        $page = new Page($this->title, $this->content);

        $this->assertSame($this->content, $page->getContent());
    }

    public function testGetAttributes(): void
    {
        $attributes = new Attributes([]);

        $page = new Page($this->title, $this->content, $attributes);

        $this->assertSame($attributes, $page->getAttributes());
    }

    public function testGetAttributesDefaultsToEmptyAttributes(): void
    {
        $page = new Page($this->title, $this->content);
        $attributes = $page->getAttributes();

        $this->assertInstanceOf(Attributes::class, $attributes);

        // Ensure it returns the same instance created when calling it the first time.
        $this->assertSame($attributes, $page->getAttributes());
    }
}
