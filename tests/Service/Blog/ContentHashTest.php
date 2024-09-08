<?php

declare(strict_types=1);

namespace App\Tests\Service\Blog;

use App\Entity\Author;
use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostStatus;
use App\Entity\PostTag;
use App\Service\Blog\ContentHash;
use App\Service\Blog\ParsedPost;
use App\Service\Blog\ParsedPostAuthor;
use App\Service\Blog\ParsedPostMetadata;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ContentHashTest extends TestCase
{
    private const string CONTENT = <<<'EOD'
        Lorem ipsum odor amet, consectetuer adipiscing elit. Sagittis vulputate cras hendrerit a tempus. Ac dolor
        ultrices vestibulum dignissim fringilla. Bibendum lacus montes ridiculus velit condimentum primis habitant.
        Montes tempor suscipit venenatis aliquam, ad bibendum. Aplatea ac; fringilla natoque tristique sociosqu semper
        torquent sodales. Quam ligula ac volutpat conubia faucibus fringilla duis. Senectus habitant ac cubilia odio
        ridiculus in risus. Nascetur mi feugiat felis cras euismod. Litora nostra natoque faucibus ac id ornare enim.
        EOD;

    private ParsedPost $parsedPost;
    private Post $post;

    protected function setUp(): void
    {
        $uuid = Uuid::fromString('0191d30c-501a-72d3-875f-07a29733a31e');

        $this->parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                $uuid,
                PostBodyType::Plaintext,
                'Lorem ipsum',
                'lorem-ipsum',
                PostStatus::Draft,
                [PostCategory::Blog],
                ['ipsum', 'lorem'],
                'Lorem ipsum odor amet, consectetuer adipiscing elit.',
                ['amet', 'lorem', 'odor', 'ipsum', 'odor'],
                'Lorem ipsum odor amet, consectetuer adipiscing elit. Sagittis vulputate cras hendrerit a tempus.',
                null,
                [],
                new DateTimeImmutable('now'),
                new DateTimeImmutable('2024-02-03 04:05:06'),
                new DateTimeImmutable('2024-03-04 05:06:07'),
            ),
            self::CONTENT,
            [
                new ParsedPostAuthor('Sam Gamgee', 'samwise@example.com'),
                new ParsedPostAuthor('Frodo Baggins', 'frodo@example.com'),
            ],
        );

        $this->post = (new Post())
            ->setId($uuid)
            ->setBodyType(PostBodyType::Plaintext)
            ->setTitle('Lorem ipsum')
            ->setSlug('lorem-ipsum')
            ->setStatus(PostStatus::Draft)
            ->setCategory([PostCategory::Blog])
            ->addTag((new PostTag())->setName('lorem'))
            ->addTag((new PostTag())->setName('ipsum'))
            ->setDescription('Lorem ipsum odor amet, consectetuer adipiscing elit.')
            ->setKeywords(['lorem', 'ipsum', 'odor', 'amet'])
            ->setExcerpt(
                'Lorem ipsum odor amet, consectetuer adipiscing elit. Sagittis vulputate cras hendrerit a tempus.',
            )
            ->setPublishedAt(new DateTimeImmutable('2024-02-03 04:05:06'))
            ->setModifiedAt(new DateTimeImmutable('2024-03-04 05:06:07'))
            ->setBody(self::CONTENT)
            ->addAuthor((new Author())->setByline('Frodo Baggins')->setEmail('frodo@example.com'))
            ->addAuthor((new Author())->setByline('Sam Gamgee')->setEmail('samwise@example.com'));
    }

    public function testContentHashFromParsedPost(): void
    {
        $contentHash = ContentHash::createFromParsedPost($this->parsedPost);

        $this->assertSame('482a8e97634d0ca6a5bb8a762bea5c98b8210954f8635d94501e2ab596de9eb2', $contentHash->getHash());
    }

    public function testContentHashFromPost(): void
    {
        $contentHash = ContentHash::createFromPost($this->post);

        $this->assertSame('482a8e97634d0ca6a5bb8a762bea5c98b8210954f8635d94501e2ab596de9eb2', $contentHash->getHash());
    }

    public function testHashesProducedAreEqual(): void
    {
        $parsedPostContentHash = ContentHash::createFromParsedPost($this->parsedPost);
        $postContentHash = ContentHash::createFromPost($this->post);

        $this->assertTrue($parsedPostContentHash->equals($postContentHash));
        $this->assertTrue($postContentHash->equals($parsedPostContentHash));
    }
}
