<?php

declare(strict_types=1);

namespace App\Tests\Service\Blog;

use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Service\Blog\StaticFileParser;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Filesystem\Filesystem;
use Webuni\FrontMatter\FrontMatterChain;

#[TestDox('StaticFileParser')]
class StaticFileParserTest extends TestCase
{
    #[TestDox('parses files with various file extensions')]
    #[TestWith([__DIR__ . '/fixtures/test-post.html', PostBodyType::Html])]
    #[TestWith([__DIR__ . '/fixtures/test-post.markdown', PostBodyType::Markdown])]
    #[TestWith([__DIR__ . '/fixtures/test-post.md', PostBodyType::Markdown])]
    #[TestWith([__DIR__ . '/fixtures/test-post.rst', PostBodyType::ReStructuredText])]
    public function testParse(string $path, PostBodyType $expectedType): void
    {
        $expectedId = Uuid::fromString('00fadd19-6120-7332-8a74-e1821536b10e');
        $expectedDate = new DateTimeImmutable('@1077451252');

        $filesystem = new Filesystem();
        $frontMatter = FrontMatterChain::create();
        $parser = new StaticFileParser($filesystem, $frontMatter);

        $parsedPost = $parser->parse($path);

        $this->assertSame($expectedId->getBytes(), $parsedPost->metadata->id->getBytes());
        $this->assertSame($expectedType, $parsedPost->metadata->contentType);
        $this->assertSame('Lorem Ipsum Odor Amet', $parsedPost->metadata->title);
        $this->assertSame('lorem-ipsum', $parsedPost->metadata->slug);
        $this->assertSame([PostCategory::Blog], $parsedPost->metadata->categories);
        $this->assertSame(['tests', 'latin'], $parsedPost->metadata->tags);
        $this->assertSame('A short description of this post.', $parsedPost->metadata->description);
        $this->assertSame(['latin', 'gibberish', 'testing'], $parsedPost->metadata->keywords);
        $this->assertSame(
            'This is an excerpt of the post. The second sentence of the excerpt. The last sentence of the excerpt.',
            $parsedPost->metadata->excerpt,
        );
        $this->assertSame('https://example.com/feed-id/123', $parsedPost->metadata->feedId);
        $this->assertSame(['layout' => 'post'], $parsedPost->metadata->additional);
        $this->assertSame($expectedDate->format('c'), $parsedPost->metadata->createdAt->format('c'));
        $this->assertNull($parsedPost->metadata->updatedAt);

        // The content should not have the front matter embedded in it.
        $this->assertStringNotContainsString('---', $parsedPost->content);
    }

    #[TestDox('throws exceptions on validation errors')]
    #[TestWith(['/path/to/file.md', 'Could not find file /path/to/file.md'])]
    #[TestWith([__DIR__ . '/fixtures/invalid-extension.txt', 'File does not have an acceptable extension:'])]
    #[TestWith([__DIR__ . '/fixtures/invalid-date.md', 'Posts must have a valid date'])]
    #[TestWith([__DIR__ . '/fixtures/missing-date.md', 'Posts must have a valid date'])]
    #[TestWith([__DIR__ . '/fixtures/invalid-updated.md', 'When provided, updated must be a valid date'])]
    #[TestWith([__DIR__ . '/fixtures/missing-title.md', 'Posts must have a title'])]
    #[TestWith([__DIR__ . '/fixtures/missing-slug.md', 'Posts must have a slug'])]
    public function testValidation(string $path, string $expectedMessage): void
    {
        $filesystem = new Filesystem();
        $frontMatter = FrontMatterChain::create();
        $parser = new StaticFileParser($filesystem, $frontMatter);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $parser->parse($path);
    }

    #[TestDox('values are set to defaults in minimal case')]
    public function testMinimal(): void
    {
        $filesystem = new Filesystem();
        $frontMatter = FrontMatterChain::create();
        $parser = new StaticFileParser($filesystem, $frontMatter);

        $parsedPost = $parser->parse(__DIR__ . '/fixtures/minimal.md');

        $this->assertSame('Lorem Ipsum Odor Amet', $parsedPost->metadata->title);
        $this->assertSame('lorem-ipsum', $parsedPost->metadata->slug);
        $this->assertInstanceOf(DateTimeImmutable::class, $parsedPost->metadata->createdAt);
        $this->assertInstanceOf(UuidInterface::class, $parsedPost->metadata->id);
        $this->assertSame([], $parsedPost->metadata->categories);
        $this->assertSame([], $parsedPost->metadata->tags);
        $this->assertNull($parsedPost->metadata->description);
        $this->assertSame([], $parsedPost->metadata->keywords);
        $this->assertNull($parsedPost->metadata->excerpt);
        $this->assertNull($parsedPost->metadata->feedId);
        $this->assertSame([], $parsedPost->metadata->additional);
        $this->assertNull($parsedPost->metadata->updatedAt);

        // The content should not have the front matter embedded in it.
        $this->assertStringNotContainsString('---', $parsedPost->content);
    }
}
