<?php

declare(strict_types=1);

namespace App\Tests\Service\Device;

use App\Entity\AnalyticsDevice;
use App\Repository\AnalyticsDeviceRepository;
use App\Service\Device\DeviceDetectorFactory;
use App\Service\Device\MatomoDeviceDetector;
use DateTimeImmutable;
use DeviceDetector\DeviceDetector;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[TestDox('MatomoDeviceDetector')]
class MatomoDeviceDetectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @param array{name?: string, category?: string, producer?: array{name: string}} $botData
     */
    #[TestDox('creates a new analytics device instance for detected values')]
    #[TestWith(['isBot' => false])]
    #[TestWith(['isBot' => true, 'expectedDevice' => 'bot'])]
    #[TestWith(['brandName' => 'UNK'])]
    #[TestWith(['brandName' => 'Acme, Inc.', 'expectedBrandName' => 'Acme, Inc.'])]
    #[TestWith([
        'isBot' => true,
        'botData' => ['producer' => ['name' => 'Bot Producer']],
        'expectedBrandName' => 'Bot Producer',
        'expectedDevice' => 'bot',
    ])]
    #[TestWith(['clientType' => 'UNK'])]
    #[TestWith(['clientType' => 'browser', 'expectedCategory' => 'browser'])]
    #[TestWith([
        'isBot' => true,
        'botData' => ['category' => 'crawler'],
        'expectedCategory' => 'crawler',
        'expectedDevice' => 'bot',
    ])]
    #[TestWith(['deviceName' => 'UNK'])]
    #[TestWith(['clientType' => 'library', 'expectedCategory' => 'library', 'expectedDevice' => 'library'])]
    #[TestWith(['deviceName' => 'Device Name', 'expectedDevice' => 'Device Name'])]
    #[TestWith(['clientEngine' => 'UNK'])]
    #[TestWith(['clientEngine' => 'Engine Name', 'expectedEngine' => 'Engine Name'])]
    #[TestWith(['clientFamily' => 'UNK'])]
    #[TestWith(['clientFamily' => 'Family Name', 'expectedFamily' => 'Family Name'])]
    #[TestWith(['clientName' => 'UNK'])]
    #[TestWith(['clientName' => 'Client Name', 'expectedName' => 'Client Name'])]
    #[TestWith([
        'isBot' => true,
        'botData' => ['name' => 'Bot Name'],
        'expectedName' => 'Bot Name',
        'expectedDevice' => 'bot',
    ])]
    #[TestWith(['osFamily' => 'UNK'])]
    #[TestWith(['osFamily' => 'OS Family Name', 'expectedOsFamily' => 'OS Family Name'])]
    public function testGetDevice(
        bool $isBot = false,
        array $botData = [],
        string $brandName = '',
        ?string $clientEngine = null,
        ?string $clientFamily = null,
        ?string $clientName = null,
        ?string $clientType = null,
        string $deviceName = '',
        ?string $osFamily = null,
        ?string $expectedBrandName = null,
        ?string $expectedCategory = null,
        string $expectedDevice = 'unknown',
        ?string $expectedEngine = null,
        ?string $expectedFamily = null,
        string $expectedName = 'unknown',
        ?string $expectedOsFamily = null,
    ): void {
        $detector = Mockery::mock(DeviceDetector::class);
        $detector->expects('parse');
        $detector->allows('isBot')->andReturn($isBot);
        $detector->allows('getBot')->andReturn($botData);
        $detector->allows('getBrandName')->andReturn($brandName);
        $detector->allows('getClient')->with('name')->andReturn($clientName);
        $detector->allows('getClient')->with('engine')->andReturn($clientEngine);
        $detector->allows('getClient')->with('family')->andReturn($clientFamily);
        $detector->allows('getClient')->with('type')->andReturn($clientType);
        $detector->allows('getDeviceName')->andReturn($deviceName);
        $detector->allows('getOs')->with('family')->andReturn($osFamily);

        $repository = Mockery::mock(AnalyticsDeviceRepository::class);
        $repository->expects('findOneBy')->andReturnNull();

        $factory = Mockery::mock(DeviceDetectorFactory::class);
        $factory->expects('createFromServerEnvironment')->with(['foo' => 'bar'])->andReturn($detector);

        $matomoDeviceDetector = new MatomoDeviceDetector($repository, $factory);
        $device = $matomoDeviceDetector->getDevice('MyAgent/1.0', ['foo' => 'bar']);

        $this->assertSame($expectedBrandName, $device->getBrandName());
        $this->assertSame($expectedCategory, $device->getCategory());
        $this->assertSame($expectedDevice, $device->getDevice());
        $this->assertSame($expectedEngine, $device->getEngine());
        $this->assertSame($expectedFamily, $device->getFamily());
        $this->assertSame($isBot, $device->isBot());
        $this->assertSame($expectedName, $device->getName());
        $this->assertSame($expectedOsFamily, $device->getOsFamily());
        $this->assertInstanceOf(DateTimeImmutable::class, $device->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $device->getUpdatedAt());
        $this->assertNull($device->getDeletedAt());
    }

    #[TestDox('finds and returns device from the analytics device repository')]
    public function testGetDeviceFromDatabase(): void
    {
        $detector = Mockery::mock(DeviceDetector::class);
        $detector->expects('parse');
        $detector->allows('isBot')->andReturn(false);
        $detector->allows('getBot')->andReturn(null);
        $detector->allows('getBrandName')->andReturn('Brand Name');
        $detector->allows('getClient')->with('name')->andReturn('Client Name');
        $detector->allows('getClient')->with('engine')->andReturn('Engine Name');
        $detector->allows('getClient')->with('family')->andReturn('Family Name');
        $detector->allows('getClient')->with('type')->andReturn('client-type');
        $detector->allows('getDeviceName')->andReturn('Device Name');
        $detector->allows('getOs')->with('family')->andReturn('OS Family Name');

        $analyticsDevice = new AnalyticsDevice();

        $repository = Mockery::mock(AnalyticsDeviceRepository::class);
        $repository
            ->expects('findOneBy')
            ->with(Mockery::capture($properties))
            ->andReturn($analyticsDevice);

        $factory = Mockery::mock(DeviceDetectorFactory::class);
        $factory->expects('createFromServerEnvironment')->with(['foo' => 'bar'])->andReturn($detector);

        $matomoDeviceDetector = new MatomoDeviceDetector($repository, $factory);
        $device = $matomoDeviceDetector->getDevice('MyAgent/1.0', ['foo' => 'bar']);

        $this->assertSame(
            [
                'brandName' => 'Brand Name',
                'category' => 'client-type',
                'device' => 'Device Name',
                'engine' => 'Engine Name',
                'family' => 'Family Name',
                'name' => 'Client Name',
                'osFamily' => 'OS Family Name',
            ],
            $properties,
        );
        $this->assertSame($analyticsDevice, $device);
    }
}
