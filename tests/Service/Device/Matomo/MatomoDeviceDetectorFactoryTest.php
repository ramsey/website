<?php

declare(strict_types=1);

namespace App\Tests\Service\Device\Matomo;

use App\Service\Device\Matomo\MatomoDeviceDetectorFactory;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;

class MatomoDeviceDetectorFactoryTest extends TestCase
{
    public function testCreateFromServerEnvironment(): void
    {
        $detectorFactory = new MatomoDeviceDetectorFactory();
        $detector = $detectorFactory->createFromServerEnvironment([]);

        $this->assertInstanceOf(DeviceDetector::class, $detector);
    }
}
