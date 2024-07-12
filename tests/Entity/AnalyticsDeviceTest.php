<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\AnalyticsDevice;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[TestDox('Analytics device entity')]
final class AnalyticsDeviceTest extends KernelTestCase
{
    #[TestDox('sets and gets')]
    #[TestWith(['setBrandName', 'getBrandName', 'Apple'])]
    #[TestWith(['setBrandName', 'getBrandName', null])]
    #[TestWith(['setCategory', 'getCategory', 'browser'])]
    #[TestWith(['setCategory', 'getCategory', null])]
    #[TestWith(['setDevice', 'getDevice', 'desktop'])]
    #[TestWith(['setEngine', 'getEngine', 'WebKit'])]
    #[TestWith(['setEngine', 'getEngine', null])]
    #[TestWith(['setFamily', 'getFamily', 'Safari'])]
    #[TestWith(['setFamily', 'getFamily', null])]
    #[TestWith(['setBot', 'isBot', true])]
    #[TestWith(['setBot', 'isBot', false])]
    #[TestWith(['setName', 'getName', 'Safari'])]
    #[TestWith(['setOsFamily', 'getOsFamily', 'Mac'])]
    #[TestWith(['setOsFamily', 'getOsFamily', null])]
    public function testSetAndGet(string $setter, string $getter, bool | string | null $value): void
    {
        $device = new AnalyticsDevice();

        $this->assertSame($device, $device->{$setter}($value));
        $this->assertSame($value, $device->{$getter}());
    }

    #[TestDox('sets the createdAt property')]
    public function testSetCreatedAt(): void
    {
        $date = new DateTime();

        $device = new AnalyticsDevice();

        $this->assertSame($device, $device->setCreatedAt($date));
        $this->assertNotSame($date, $device->getCreatedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $device->getCreatedAt());
        $this->assertSame($date->format('c'), $device->getCreatedAt()->format('c'));
    }

    #[TestDox('sets the updatedAt property')]
    public function testSetUpdatedAt(): void
    {
        $date = new DateTime();

        $device = new AnalyticsDevice();

        $this->assertSame($device, $device->setUpdatedAt($date));
        $this->assertNotSame($date, $device->getUpdatedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $device->getUpdatedAt());
        $this->assertSame($date->format('c'), $device->getUpdatedAt()->format('c'));
    }

    #[TestDox('sets the deletedAt property')]
    public function testSetDeletedAt(): void
    {
        $date = new DateTime();

        $device = new AnalyticsDevice();

        $this->assertSame($device, $device->setDeletedAt($date));
        $this->assertNotSame($date, $device->getDeletedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $device->getDeletedAt());
        $this->assertSame($date->format('c'), $device->getDeletedAt()->format('c'));
    }
}
