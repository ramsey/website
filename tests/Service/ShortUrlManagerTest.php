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
use PHPUnit\Framework\TestCase;

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

    public function testBuildUrlWithCustomSlug(): void
    {
        $shortUrl = new ShortUrl();
        $shortUrl->setCustomSlug('custom-slug');

        $this->assertSame('https://example.com/custom-slug', (string) $this->manager->buildUrl($shortUrl));
    }

    public function testBuildUrlWithoutCustomSlug(): void
    {
        $shortUrl = new ShortUrl();
        $shortUrl->setSlug('non-custom-slug');

        $this->assertSame('https://example.com/non-custom-slug', (string) $this->manager->buildUrl($shortUrl));
    }

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
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getUpdatedAt());
    }

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
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getUpdatedAt());
    }

    public function testCreateShortUrlThrowsExceptionForInvalidCustomSlug(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $this->repository->expects('findOneBySlug')->never();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid custom slug: foo bar baz');

        $this->manager->createShortUrl($url, 'foo bar baz');
    }

    public function testSoftDeleteShortUrl(): void
    {
        $shortUrl = new ShortUrl();

        $this->assertNull($shortUrl->getDeletedAt());

        $this->manager->softDeleteShortUrl($shortUrl);

        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getDeletedAt());
    }
}
