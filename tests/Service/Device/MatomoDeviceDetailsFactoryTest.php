<?php

declare(strict_types=1);

namespace App\Tests\Service\Device;

use App\Service\Device\Matomo\DeviceDetectorFactory;
use App\Service\Device\MatomoDeviceDetailsFactory;
use DeviceDetector\DeviceDetector;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[TestDox('MatomoDeviceDetailsFactory')]
class MatomoDeviceDetailsFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @param array{name?: string, category?: string, producer?: array{name: string}} $botData
     */
    #[TestDox('creates a new device details instance for detected values')]
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
        'botData' => ['category' => 'Crawler'],
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

        $deviceDetectorFactory = Mockery::mock(DeviceDetectorFactory::class);
        $deviceDetectorFactory->expects('createFromServerEnvironment')->with(['foo' => 'bar'])->andReturn($detector);

        $matomoDeviceDetailsFactory = new MatomoDeviceDetailsFactory($deviceDetectorFactory);
        $details = $matomoDeviceDetailsFactory->createFromServerEnvironment(['foo' => 'bar']);

        $this->assertSame($expectedBrandName, $details->brandName);
        $this->assertSame($expectedCategory, $details->category);
        $this->assertSame($expectedDevice, $details->type);
        $this->assertSame($expectedEngine, $details->engine);
        $this->assertSame($expectedFamily, $details->family);
        $this->assertSame($isBot, $details->isBot);
        $this->assertSame($expectedName, $details->name);
        $this->assertSame($expectedOsFamily, $details->osFamily);
    }
}
