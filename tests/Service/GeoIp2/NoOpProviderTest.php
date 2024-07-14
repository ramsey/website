<?php

declare(strict_types=1);

namespace App\Tests\Service\GeoIp2;

use App\Service\GeoIp2\NoOpProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('GeoIp2 NoOpProvider')]
class NoOpProviderTest extends TestCase
{
    #[TestDox('returns an empty City model')]
    public function testCity(): void
    {
        $provider = new NoOpProvider();
        $city = $provider->city('anIpAddress');

        $this->assertNull($city->city->name);
        $this->assertNull($city->country->isoCode);
        $this->assertNull($city->location->latitude);
        $this->assertNull($city->location->longitude);
        $this->assertNull($city->mostSpecificSubdivision->isoCode);
    }

    #[TestDox('returns an empty Country model')]
    public function testCountry(): void
    {
        $provider = new NoOpProvider();
        $country = $provider->country('anIpAddress');

        $this->assertNull($country->country->name);
    }
}
