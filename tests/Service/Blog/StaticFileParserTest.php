<?php

declare(strict_types=1);

namespace App\Tests\Service\Blog;

use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Service\Blog\ParsedPostAuthor;
use App\Service\Blog\StaticFileParser;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use PhpExtended\Email\MailboxListParser;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\AsciiSlugger;
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
        $expectedCreated = new DateTimeImmutable('2004-02-22 12:00:52 +00:00');
        $expectedPublished = new DateTimeImmutable('2004-02-23 15:22:15 +00:00');
        $expectedModified = new DateTimeImmutable('2009-11-16 03:11:19 +00:00');

        $filesystem = new Filesystem();
        $frontMatter = FrontMatterChain::create();
        $emailParser = new MailboxListParser();
        $slugger = new AsciiSlugger();
        $parser = new StaticFileParser($filesystem, $frontMatter, $emailParser, $slugger);

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
        $this->assertSame($expectedCreated->format('U'), $parsedPost->metadata->createdAt?->format('U'));
        $this->assertSame($expectedPublished->format('U'), $parsedPost->metadata->publishedAt?->format('U'));
        $this->assertSame($expectedModified->format('U'), $parsedPost->metadata->modifiedAt?->format('U'));

        $this->assertCount(4, $parsedPost->authors);
        $this->assertContainsOnlyInstancesOf(ParsedPostAuthor::class, $parsedPost->authors);
        $this->assertSame('Frodo Baggins', $parsedPost->authors[0]->byline);
        $this->assertSame('frodo@example.com', $parsedPost->authors[0]->email);
        $this->assertSame('Samwise Gamgee', $parsedPost->authors[1]->byline);
        $this->assertSame('samwise@example.com', $parsedPost->authors[1]->email);
        $this->assertSame('Peregrin Took', $parsedPost->authors[2]->byline);
        $this->assertSame('pippin@example.com', $parsedPost->authors[2]->email);
        $this->assertSame('', $parsedPost->authors[3]->byline);
        $this->assertSame('merry@example.com', $parsedPost->authors[3]->email);

        // The content should not have the front matter embedded in it.
        $this->assertStringNotContainsString('---', $parsedPost->content);
    }

    #[TestDox('throws exceptions on validation errors')]
    #[TestWith(['/path/to/file.md', 'Could not find file /path/to/file.md'])]
    #[TestWith([__DIR__ . '/fixtures/invalid-extension.txt', 'File does not have an acceptable extension:'])]
    #[TestWith([__DIR__ . '/fixtures/invalid-created.md', 'When provided, created must be a valid date'])]
    #[TestWith([__DIR__ . '/fixtures/invalid-published.md', 'When provided, published must be a valid date'])]
    #[TestWith([__DIR__ . '/fixtures/invalid-modified.md', 'When provided, modified must be a valid date'])]
    #[TestWith([__DIR__ . '/fixtures/missing-title.md', 'Posts must have a title'])]
    #[TestWith([__DIR__ . '/fixtures/missing-status.md', 'Posts must have a valid status'])]
    #[TestWith([__DIR__ . '/fixtures/invalid-authors.md', 'When provided, authors must have valid mailbox strings'])]
    public function testValidation(string $path, string $expectedMessage): void
    {
        $filesystem = new Filesystem();
        $frontMatter = FrontMatterChain::create();
        $emailParser = new MailboxListParser();
        $slugger = new AsciiSlugger();
        $parser = new StaticFileParser($filesystem, $frontMatter, $emailParser, $slugger);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $parser->parse($path);
    }

    #[TestDox('values are set to defaults in minimal case')]
    public function testMinimal(): void
    {
        $filesystem = new Filesystem();
        $frontMatter = FrontMatterChain::create();
        $emailParser = new MailboxListParser();
        $slugger = new AsciiSlugger();
        $parser = new StaticFileParser($filesystem, $frontMatter, $emailParser, $slugger);

        $parsedPost = $parser->parse(__DIR__ . '/fixtures/minimal.md');

        $this->assertSame('Lorem Ipsum Odor Amet', $parsedPost->metadata->title);
        $this->assertSame('lorem-ipsum-odor-amet', $parsedPost->metadata->slug);
        $this->assertInstanceOf(UuidInterface::class, $parsedPost->metadata->id);
        $this->assertSame([], $parsedPost->metadata->categories);
        $this->assertSame([], $parsedPost->metadata->tags);
        $this->assertNull($parsedPost->metadata->description);
        $this->assertSame([], $parsedPost->metadata->keywords);
        $this->assertNull($parsedPost->metadata->excerpt);
        $this->assertNull($parsedPost->metadata->feedId);
        $this->assertSame([], $parsedPost->metadata->additional);
        $this->assertNull($parsedPost->metadata->createdAt);
        $this->assertNull($parsedPost->metadata->publishedAt);
        $this->assertNull($parsedPost->metadata->modifiedAt);
        $this->assertSame([], $parsedPost->authors);

        // The content should not have the front matter embedded in it.
        $this->assertStringNotContainsString('---', $parsedPost->content);
    }
}
