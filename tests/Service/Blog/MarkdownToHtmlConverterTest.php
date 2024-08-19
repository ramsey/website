<?php

declare(strict_types=1);

namespace App\Tests\Service\Blog;

use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Service\Blog\MarkdownToHtmlConverter;
use App\Service\Blog\UnsupportedPostBodyType;
use League\CommonMark\CommonMarkConverter;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('MarkdownToHtmlConverter')]
class MarkdownToHtmlConverterTest extends TestCase
{
    #[TestDox('converts Markdown to HTML')]
    public function testConvert(): void
    {
        $commonMarkConverter = new CommonMarkConverter();
        $postBodyConverter = new MarkdownToHtmlConverter($commonMarkConverter);

        $post = (new Post())
            ->setBodyType(PostBodyType::Markdown)
            ->setBody('# Hello World!');

        $this->assertSame("<h1>Hello World!</h1>\n", $postBodyConverter->convert($post));
    }

    #[TestDox('throws an exception if post body type is not supported')]
    public function testConvertThrows(): void
    {
        $commonMarkConverter = new CommonMarkConverter();
        $postBodyConverter = new MarkdownToHtmlConverter($commonMarkConverter);

        $post = (new Post())
            ->setBodyType(PostBodyType::ReStructuredText)
            ->setBody('# Hello World!');

        $this->expectException(UnsupportedPostBodyType::class);
        $this->expectExceptionMessage(MarkdownToHtmlConverter::class . ' does not support reStructuredText');

        $postBodyConverter->convert($post);
    }
}
