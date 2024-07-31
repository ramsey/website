<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\ShortUrl;
use App\Repository\ShortUrlRepository;
use App\Service\Codec\Base62Codec;
use App\Service\ShortUrlManager;
use DateTimeImmutable;
use InvalidArgumentException;
use Laminas\Diactoros\UriFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('ShortUrlManager')]
final class ShortUrlManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ShortUrlManager $manager;
    private ShortUrlRepository & MockInterface $repository;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(ShortUrlRepository::class);
        $this->manager = new ShortUrlManager(
            'https://example.com/',
            $this->repository,
            new UriFactory(),
            new Base62Codec(),
        );
    }

    #[TestDox('builds a URL with a custom slug')]
    public function testBuildUrlWithCustomSlug(): void
    {
        $shortUrl = new ShortUrl();
        $shortUrl->setCustomSlug('custom-slug');

        $this->assertSame('https://example.com/custom-slug', (string) $this->manager->buildUrl($shortUrl));
    }

    #[TestDox('builds a URL with a random slug')]
    public function testBuildUrlWithoutCustomSlug(): void
    {
        $shortUrl = new ShortUrl();
        $shortUrl->setSlug('non-custom-slug');

        $this->assertSame('https://example.com/non-custom-slug', (string) $this->manager->buildUrl($shortUrl));
    }

    #[TestDox('::buildUrl() returns NULL when the ShortUrl does not have a custom slug and it has not been saved')]
    public function testBuildUrlWithUnsavedShortUrl(): void
    {
        $shortUrl = new ShortUrl();

        $this->assertNull($this->manager->buildUrl($shortUrl));
    }

    #[TestDox('creates a short URL instance with random slug')]
    public function testCreateShortUrl(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $this->repository
            ->expects('findOneBySlug')
            ->with(Mockery::type('string'))
            ->andReturn(null);

        $shortUrl = $this->manager->createShortUrl($url);

        $this->assertSame($url, (string) $shortUrl->getDestinationUrl());
        $this->assertIsString($shortUrl->getSlug());
        $this->assertNull($shortUrl->getCustomSlug());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getCreatedAt());
        $this->assertNull($shortUrl->getUpdatedAt());
    }

    #[TestDox('creates a short URL, generating a random slug until it is unique')]
    public function testCreateShortUrlGeneratesSameSlugMultipleTimes(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $this->repository
            ->expects('findOneBySlug')
            ->with(Mockery::type('string'))
            ->times(4)
            ->andReturn(new ShortUrl(), new ShortUrl(), new ShortUrl(), null);

        $shortUrl = $this->manager->createShortUrl($url);

        $this->assertSame($url, (string) $shortUrl->getDestinationUrl());
        $this->assertIsString($shortUrl->getSlug());
        $this->assertNull($shortUrl->getCustomSlug());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getCreatedAt());
        $this->assertNull($shortUrl->getUpdatedAt());
    }

    #[TestDox('throws an exception when trying to create a short URL with an invalid custom slug')]
    public function testCreateShortUrlThrowsExceptionForInvalidCustomSlug(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $this->repository->expects('findOneBySlug')->never();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid custom slug: foo bar baz');

        $this->manager->createShortUrl($url, 'foo bar baz');
    }

    #[TestDox('throws an exception when trying to create a short URL with an existing custom slug')]
    public function testCreateShortUrlThrowsExceptionForExistingCustomSlug(): void
    {
        $url = 'https://example.com/a-long-url-using-existing-custom-slug';

        $this->repository->expects('findOneBySlug')->never();
        $this->repository
            ->expects('findOneByCustomSlug')
            ->with('already-exists')
            ->andReturn(new ShortUrl());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom slug already exists: already-exists');

        $this->manager->createShortUrl($url, 'already-exists');
    }

    #[TestDox('sets the deletedAt property to soft-delete a short URL')]
    public function testSoftDeleteShortUrl(): void
    {
        $shortUrl = new ShortUrl();

        $this->assertNull($shortUrl->getDeletedAt());

        $this->manager->softDeleteShortUrl($shortUrl);

        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getDeletedAt());
    }

    #[TestDox('::getRepository() returns a ShortUrlRepository')]
    public function testGetRepository(): void
    {
        $this->assertSame($this->repository, $this->manager->getRepository());
    }

    #[TestDox("generates a random slug for the ShortUrl, if it doesn't have one")]
    public function testCheckAndSetSlugGeneratesRandomSlug(): void
    {
        $this->repository
            ->expects('findOneBySlug')
            ->with(Mockery::type('string'))
            ->times(4)
            ->andReturn(new ShortUrl(), new ShortUrl(), new ShortUrl(), null);

        $shortUrl = new ShortUrl();

        $this->assertNull($shortUrl->getSlug());
        $this->assertSame($shortUrl, $this->manager->checkAndSetSlug($shortUrl));
        $this->assertIsString($shortUrl->getSlug());
    }

    #[TestDox('skips generating a random slug, if the ShortUrl already has one')]
    public function testCheckAndSetSlugWhenShortUrlHasSlug(): void
    {
        $slug = 'short-url-already-has-slug';

        $this->repository->expects('findOneBySlug')->never();

        $shortUrl = (new ShortUrl())->setSlug($slug);

        $this->assertSame($slug, $shortUrl->getSlug());
        $this->assertSame($shortUrl, $this->manager->checkAndSetSlug($shortUrl));
        $this->assertSame($slug, $shortUrl->getSlug());
    }
}
