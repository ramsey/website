<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\ShortUrl;
use App\Repository\ShortUrlRepository;
use App\Service\Codec\Base62Codec;
use App\Service\ShortUrlManager;
use App\Tests\Doctrine\Collections\LazyArrayCollection;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use InvalidArgumentException;
use Laminas\Diactoros\UriFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
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
            ->expects('matching')
            ->with(Mockery::type(Criteria::class))
            ->andReturn(new LazyArrayCollection([]));

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

    #[TestDox('createShortUrl() returns existing ShortUrl for same destination URL, without custom slug')]
    public function testCreateShortUrlFindsExistingUrl(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $dbResults = [
            (new ShortUrl())->setSlug('F00'),
            (new ShortUrl())->setSlug('B4R'),
        ];

        $this->repository
            ->expects('matching')
            ->with(Mockery::type(Criteria::class))
            ->andReturn(new LazyArrayCollection($dbResults));

        $shortUrl = $this->manager->createShortUrl($url);

        $this->assertSame($dbResults[0], $shortUrl);
    }

    #[TestDox('createShortUrl() returns existing ShortUrl for same destination URL, with custom slug')]
    public function testCreateShortUrlFindsExistingUrlWithCustomSlug(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $dbResults = [
            (new ShortUrl())->setSlug('F00'),
            (new ShortUrl())->setSlug('B4R')->setCustomSlug('custom'),
            (new ShortUrl())->setSlug('B4Z'),
        ];

        $this->repository
            ->expects('matching')
            ->with(Mockery::type(Criteria::class))
            ->andReturn(new LazyArrayCollection($dbResults));

        $shortUrl = $this->manager->createShortUrl($url);

        $this->assertSame($dbResults[1], $shortUrl);
    }

    #[TestDox('createShortUrl() returns existing ShortUrl for same destination URL, with matching custom slug')]
    public function testCreateShortUrlFindsExistingUrlWithMatchingCustomSlug(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $dbResults = [
            (new ShortUrl())->setSlug('F00'),
            (new ShortUrl())->setSlug('B4R')->setCustomSlug('custom'),
            (new ShortUrl())->setSlug('B4Z')->setCustomSlug('matching-custom-slug'),
        ];

        $this->repository
            ->expects('matching')
            ->with(Mockery::type(Criteria::class))
            ->andReturn(new LazyArrayCollection($dbResults));

        $shortUrl = $this->manager->createShortUrl($url, 'matching-custom-slug');

        $this->assertSame($dbResults[2], $shortUrl);
    }

    #[TestDox('createShortUrl() returns existing ShortUrl for same destination URL and updates it with custom slug')]
    public function testCreateShortUrlFindsExistingUrlAndUpdatesWithCustomSlug(): void
    {
        $url = 'https://example.com/this-is-a-long-url';
        $createdAt = new DateTime('last week');
        $updatedAt = new DateTime('last week');

        $dbResults = [
            (new ShortUrl())->setSlug('F00')->setCustomSlug('non-matching'),
            (new ShortUrl())->setSlug('B4R')->setCreatedAt($createdAt)->setUpdatedAt($updatedAt),
            (new ShortUrl())->setSlug('B4Z')->setCustomSlug('another-custom-slug'),
        ];

        $this->repository
            ->expects('matching')
            ->with(Mockery::type(Criteria::class))
            ->andReturn(new LazyArrayCollection($dbResults));

        $shortUrl = $this->manager->createShortUrl($url, 'custom-slug_123.ABC');

        $this->assertSame($dbResults[1], $shortUrl);
        $this->assertSame('custom-slug_123.ABC', $shortUrl->getCustomSlug());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getCreatedAt());
        $this->assertSame($createdAt->format('c'), $shortUrl->getCreatedAt()->format('c'));
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getUpdatedAt());
        $this->assertNotSame($updatedAt->format('c'), $shortUrl->getUpdatedAt()->format('c'));
    }

    #[TestDox('createShortUrl() creates a new ShortUrl for existing destination URL for a new custom slug')]
    public function testCreateShortUrlCreatesNewShortUrlForExistingDestinationUrl(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $dbResults = [
            (new ShortUrl())->setSlug('F00')->setCustomSlug('non-matching'),
            (new ShortUrl())->setSlug('B4Z')->setCustomSlug('another-custom-slug'),
        ];

        $this->repository
            ->expects('matching')
            ->with(Mockery::type(Criteria::class))
            ->andReturn(new LazyArrayCollection($dbResults));

        $this->repository
            ->expects('findOneBySlug')
            ->with(Mockery::type('string'))
            ->andReturn(null);

        $shortUrl = $this->manager->createShortUrl($url, 'new-custom-slug');

        $this->assertNotContains($shortUrl, $dbResults);
        $this->assertSame($url, (string) $shortUrl->getDestinationUrl());
        $this->assertIsString($shortUrl->getSlug());
        $this->assertSame('new-custom-slug', $shortUrl->getCustomSlug());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $shortUrl->getUpdatedAt());
    }

    public function testCreateShortUrlGeneratesSameSlugMultipleTimes(): void
    {
        $url = 'https://example.com/this-is-a-long-url';

        $this->repository
            ->expects('matching')
            ->with(Mockery::type(Criteria::class))
            ->andReturn(new LazyArrayCollection([]));

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

        $this->repository
            ->expects('matching')
            ->with(Mockery::type(Criteria::class))
            ->andReturn(new LazyArrayCollection([]));

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
