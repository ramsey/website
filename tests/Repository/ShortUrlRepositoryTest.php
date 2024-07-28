<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\ShortUrl;
use App\Repository\ShortUrlRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function count;

#[Group('db')]
class ShortUrlRepositoryTest extends KernelTestCase
{
    private ShortUrlRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(ShortUrl::class);
    }

    public function testFindAll(): void
    {
        $shortUrls = $this->repository->findAll();

        $this->assertGreaterThan(0, count($shortUrls));
        $this->assertContainsOnlyInstancesOf(ShortUrl::class, $shortUrls);
    }

    public function testFindAllWithCriteria(): void
    {
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->isNull('deletedAt'));

        /** @var Collection<int, ShortUrl> $shortUrls */
        $shortUrls = $this->repository->matching($criteria);

        $this->assertGreaterThan(0, count($shortUrls));

        foreach ($shortUrls as $shortUrl) {
            $this->assertNull($shortUrl->getDeletedAt());
            $this->assertInstanceOf(UuidInterface::class, $shortUrl->getId());
        }
    }

    public function testFindOneByCustomSlug(): void
    {
        $shortUrl = $this->repository->findOneByCustomSlug('custom1');

        $this->assertSame('https://example.com/another-long-url', (string) $shortUrl?->getDestinationUrl());
        $this->assertSame('custom1', $shortUrl?->getCustomSlug());
        $this->assertInstanceOf(UuidInterface::class, $shortUrl->getId());
    }

    public function testFindOneBySlug(): void
    {
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->isNull('deletedAt'));

        /** @var Collection<int, ShortUrl> $shortUrls */
        $shortUrls = $this->repository->matching($criteria);

        $shortUrl = $this->repository->findOneBySlug((string) $shortUrls[0]?->getSlug());

        $this->assertSame('https://example.com/this-is-a-long-url', (string) $shortUrl?->getDestinationUrl());
        $this->assertSame($shortUrls[0]?->getSlug(), $shortUrl?->getSlug());
        $this->assertInstanceOf(UuidInterface::class, $shortUrl?->getId());
    }

    #[TestDox('returns null when the short URL host name does not match')]
    public function testGetShortUrlForShortUrlWhenHostnameNotMatch(): void
    {
        $this->assertNull($this->repository->getShortUrlForShortUrl('https://example.com/foo'));
    }

    #[TestDox('returns null when a ShortUrl is not found for the given short URL')]
    public function testGetShortUrlForShortUrlWhenNotFound(): void
    {
        $url = new Uri('https://bram.se/this-is-not-in-the-database');

        $this->assertNull($this->repository->getShortUrlForShortUrl($url));
    }

    #[TestDox('returns null when a ShortUrl is found for a custom slug')]
    public function testGetShortUrlForShortUrlWithCustomSlug(): void
    {
        $url = new Uri('https://bram.se/this-is-a-custom-slug');

        $this->assertInstanceOf(ShortUrl::class, $this->repository->getShortUrlForShortUrl($url));
    }

    #[TestDox('returns null when a ShortUrl is found for a randomized slug')]
    public function testGetShortUrlForShortUrlWithRandomizedSlug(): void
    {
        $url = new Uri('https://bram.se/F0084R');

        $this->assertInstanceOf(ShortUrl::class, $this->repository->getShortUrlForShortUrl($url));
    }
}
