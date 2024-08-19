<?php

declare(strict_types=1);

namespace App\Tests\Service\Blog;

use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Service\Blog\MarkdownToHtmlConverter;
use App\Service\Blog\PostBodyToHtmlConverter;
use App\Service\Blog\UnsupportedPostBodyType;
use League\CommonMark\CommonMarkConverter;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('PostBodyToHtmlConverter')]
class PostBodyToHtmlConverterTest extends TestCase
{
    private PostBodyToHtmlConverter $converter;

    protected function setUp(): void
    {
        $commonMarkConverter = new CommonMarkConverter();
        $markdownToHtmlConverter = new MarkdownToHtmlConverter($commonMarkConverter);

        $this->converter = new PostBodyToHtmlConverter($markdownToHtmlConverter);
    }

    #[TestDox('converts Markdown to HTML')]
    public function testConvertMarkdown(): void
    {
        $post = (new Post())
            ->setBodyType(PostBodyType::Markdown)
            ->setBody('# Hello World!');

        $this->assertSame("<h1>Hello World!</h1>\n", $this->converter->convert($post));
    }

    #[TestDox('returns the same HTML when provided HTML')]
    public function testConvertHtml(): void
    {
        $post = (new Post())
            ->setBodyType(PostBodyType::Html)
            ->setBody('<p>Hello World!</p>');

        $this->assertSame('<p>Hello World!</p>', $this->converter->convert($post));
    }

    #[TestDox('throws an exception if the post body type is not supported')]
    public function testConvertThrows(): void
    {
        $post = (new Post())
            ->setBodyType(PostBodyType::Plaintext)
            ->setBody('Hello World!');

        $this->expectException(UnsupportedPostBodyType::class);
        $this->expectExceptionMessage(PostBodyToHtmlConverter::class . ' does not support plaintext');

        $this->converter->convert($post);
    }
}
