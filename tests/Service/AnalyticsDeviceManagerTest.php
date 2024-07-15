<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\AnalyticsDevice;
use App\Repository\AnalyticsDeviceRepository;
use App\Service\AnalyticsDeviceManager;
use App\Service\Device\DeviceDetails;
use App\Service\Device\DeviceDetailsFactory;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('AnalyticsDeviceManager')]
class AnalyticsDeviceManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox('creates a new analytics device instance for details')]
    public function testGetDevice(): void
    {
        $details = new DeviceDetails(
            name: 'A New Browser',
            type: 'desktop',
            brandName: 'Company Name',
            category: 'browser',
            engine: 'Blink',
            family: 'Chromium',
            osFamily: 'Mac',
        );

        $repository = Mockery::mock(AnalyticsDeviceRepository::class);
        $repository
            ->expects('findOneBy')
            ->with(Mockery::capture($dbLookupProperties))
            ->andReturnNull();

        $factory = Mockery::mock(DeviceDetailsFactory::class);
        $factory->expects('createFromServerEnvironment')->with(['foo' => 'bar'])->andReturn($details);

        $matomoDeviceDetector = new AnalyticsDeviceManager($repository, $factory);
        $device = $matomoDeviceDetector->getDevice(['foo' => 'bar']);

        $this->assertSame('Company Name', $device->getBrandName());
        $this->assertSame('browser', $device->getCategory());
        $this->assertSame('desktop', $device->getDevice());
        $this->assertSame('Blink', $device->getEngine());
        $this->assertSame('Chromium', $device->getFamily());
        $this->assertFalse($device->isBot());
        $this->assertSame('A New Browser', $device->getName());
        $this->assertSame('Mac', $device->getOsFamily());
        $this->assertInstanceOf(DateTimeImmutable::class, $device->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $device->getUpdatedAt());
        $this->assertNull($device->getDeletedAt());

        $this->assertSame([
            'brandName' => 'Company Name',
            'category' => 'browser',
            'device' => 'desktop',
            'engine' => 'Blink',
            'family' => 'Chromium',
            'name' => 'A New Browser',
            'osFamily' => 'Mac',
        ], $dbLookupProperties);
    }

    #[TestDox('finds and returns device from the analytics device repository')]
    public function testGetDeviceFromDatabase(): void
    {
        $details = new DeviceDetails(
            name: 'Some Crawler',
            type: 'bot',
            brandName: 'Sneaky Company',
            category: 'crawler',
            isBot: true,
        );

        $analyticsDevice = new AnalyticsDevice();

        $repository = Mockery::mock(AnalyticsDeviceRepository::class);
        $repository
            ->expects('findOneBy')
            ->with(Mockery::capture($dbLookupProperties))
            ->andReturn($analyticsDevice);

        $factory = Mockery::mock(DeviceDetailsFactory::class);
        $factory->expects('createFromServerEnvironment')->with(['foo' => 'bar'])->andReturn($details);

        $matomoDeviceDetector = new AnalyticsDeviceManager($repository, $factory);
        $device = $matomoDeviceDetector->getDevice(['foo' => 'bar']);

        $this->assertSame($analyticsDevice, $device);

        $this->assertSame([
            'brandName' => 'Sneaky Company',
            'category' => 'crawler',
            'device' => 'bot',
            'engine' => null,
            'family' => null,
            'name' => 'Some Crawler',
            'osFamily' => null,
        ], $dbLookupProperties);
    }
}
