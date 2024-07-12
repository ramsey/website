<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\AnalyticsDevice;
use App\Repository\AnalyticsDeviceRepository;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('db')]
#[TestDox('AnalyticsDeviceRepository')]
class AnalyticsDeviceRepositoryTest extends KernelTestCase
{
    private AnalyticsDeviceRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(AnalyticsDevice::class);
    }

    #[TestDox('finds an analytics device in the database')]
    public function testFindOneBy(): void
    {
        $device = $this->repository->findOneBy([
            'brandName' => 'Apple',
            'category' => 'browser',
            'device' => 'desktop',
            'engine' => 'WebKit',
            'family' => 'Safari',
            'name' => 'Safari',
            'osFamily' => 'Mac',
        ]);

        $this->assertInstanceOf(AnalyticsDevice::class, $device);
        $this->assertInstanceOf(UuidInterface::class, $device->getId());
        $this->assertSame('Apple', $device->getBrandName());
        $this->assertSame('browser', $device->getCategory());
        $this->assertSame('desktop', $device->getDevice());
        $this->assertSame('WebKit', $device->getEngine());
        $this->assertSame('Safari', $device->getFamily());
        $this->assertFalse($device->isBot());
        $this->assertSame('Safari', $device->getName());
        $this->assertInstanceOf(DateTimeImmutable::class, $device->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $device->getUpdatedAt());
        $this->assertNull($device->getDeletedAt());
    }
}
