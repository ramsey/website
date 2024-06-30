<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\ShortUrl;
use App\Entity\User;
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
        $user = new User();

        $this->repository
            ->expects('findOneBySlug')
            ->with(Mockery::type('string'))
            ->andReturn(null);

        $shortUrl = $this->manager->createShortUrl($url, $user);

        $this->assertSame($url, (string) $shortUrl->getDestinationUrl());
        $this->assertIsString($shortUrl->getSlug());
        $this->assertNull($shortUrl->getCustomSlug());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getUpdatedAt());
        $this->assertSame($user, $shortUrl->getCreatedBy());
        $this->assertSame($user, $shortUrl->getUpdatedBy());
    }

    #[TestDox('creates a short URL, generating a random slug until it is unique')]
    public function testCreateShortUrlGeneratesSameSlugMultipleTimes(): void
    {
        $url = 'https://example.com/this-is-a-long-url';
        $user = new User();

        $this->repository
            ->expects('findOneBySlug')
            ->with(Mockery::type('string'))
            ->times(4)
            ->andReturn(new ShortUrl(), new ShortUrl(), new ShortUrl(), null);

        $shortUrl = $this->manager->createShortUrl($url, $user);

        $this->assertSame($url, (string) $shortUrl->getDestinationUrl());
        $this->assertIsString($shortUrl->getSlug());
        $this->assertNull($shortUrl->getCustomSlug());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getUpdatedAt());
        $this->assertSame($user, $shortUrl->getCreatedBy());
        $this->assertSame($user, $shortUrl->getUpdatedBy());
    }

    #[TestDox('throws an exception when trying to create a short URL with an invalid custom slug')]
    public function testCreateShortUrlThrowsExceptionForInvalidCustomSlug(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $this->repository->expects('findOneBySlug')->never();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid custom slug: foo bar baz');

        $this->manager->createShortUrl($url, new User(), 'foo bar baz');
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

        $this->manager->createShortUrl($url, new User(), 'already-exists');
    }

    #[TestDox('sets the deletedAt property to soft-delete a short URL')]
    public function testSoftDeleteShortUrl(): void
    {
        $shortUrl = new ShortUrl();
        $user = new User();

        $this->assertNull($shortUrl->getDeletedAt());
        $this->assertNull($shortUrl->getUpdatedBy());

        $this->manager->softDeleteShortUrl($shortUrl, $user);

        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getDeletedAt());
        $this->assertSame($user, $shortUrl->getUpdatedBy());
    }
}
