<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\AnalyticsDevice;
use App\Entity\AnalyticsEvent;
use App\Repository\AnalyticsEventRepository;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function json_encode;
use function strlen;

#[Group('db')]
#[TestDox('AnalyticsEventRepository')]
class AnalyticsEventRepositoryTest extends KernelTestCase
{
    private AnalyticsEventRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(AnalyticsEvent::class);
    }

    #[TestDox('finds an analytics event in the database')]
    public function testFindOneBy(): void
    {
        // Get the first event in the database.
        /** @var AnalyticsEvent $event */
        $event = $this->repository->findAll()[0];

        $this->assertInstanceOf(AnalyticsEvent::class, $event);
        $this->assertInstanceOf(UuidInterface::class, $event->getId());
        $this->assertInstanceOf(AnalyticsDevice::class, $event->getDevice());
        $this->assertIsString($event->getGeoCity());
        $this->assertGreaterThan(0, strlen($event->getGeoCity()));
        $this->assertIsString($event->getGeoCountryCode());
        $this->assertSame(2, strlen($event->getGeoCountryCode()));
        $this->assertIsFloat($event->getGeoLatitude());
        $this->assertIsFloat($event->getGeoLongitude());
        $this->assertIsString($event->getGeoSubdivisionCode());
        $this->assertGreaterThan(0, strlen($event->getGeoSubdivisionCode()));
        $this->assertIsString($event->getHostname());
        $this->assertGreaterThan(0, strlen($event->getHostname()));
        $this->assertIsString($event->getIpUserAgentHash());
        $this->assertSame(20, strlen($event->getIpUserAgentHash()));
        $this->assertIsString($event->getLocale());
        $this->assertGreaterThan(0, strlen($event->getLocale()));
        $this->assertSame('pageview', $event->getName());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode(['foo' => 'bar', 'baz' => 'qux']),
            (string) json_encode($event->getTags()),
        );
        $this->assertGreaterThan(0, strlen($event->getUri()));
        $this->assertGreaterThan(0, strlen($event->getUserAgent()));
        $this->assertInstanceOf(DateTimeImmutable::class, $event->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $event->getUpdatedAt());
        $this->assertNull($event->getDeletedAt());
    }
}
