<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\AnalyticsDevice;
use App\Service\Analytics\AnalyticsDetails;
use App\Service\AnalyticsEventManager;
use App\Service\Device\DeviceService;
use DateTimeImmutable;
use Laminas\Diactoros\UriFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('AnalyticsEventManager')]
class AnalyticsEventManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox('creates an analytics event instance from analytics details')]
    public function testCreateAnalyticsEventFromDetails(): void
    {
        $device = new AnalyticsDevice();

        $deviceService = Mockery::mock(DeviceService::class);
        $deviceService
            ->expects('getDevice')
            ->with('MyAgent/123.456.789', ['HTTP_SOME_HEADER' => 'header value'])
            ->andReturn($device);

        $details = new AnalyticsDetails(
            eventName: 'a-test-event',
            url: (new UriFactory())->createUri('https://example.com/'),
            geoCity: 'Nashville',
            geoCountryCode: 'US',
            geoLatitude: 36.162222,
            geoLongitude: -86.774444,
            geoSubdivisionCode: 'TN',
            ipAddress: '127.0.0.1',
            ipAddressUserAgentHash: 'anIpAddressAndUserAgentHash',
            locale: 'en-GB',
            redirectUrl: (new UriFactory())->createUri('https://example.com/redirect'),
            referrer: (new UriFactory())->createUri('https://example.com/referrer'),
            serverEnvironment: ['HTTP_SOME_HEADER' => 'header value'],
            tags: ['foo' => 'bar'],
            userAgent: 'MyAgent/123.456.789',
        );

        $analyticsEventManager = new AnalyticsEventManager($deviceService);
        $analyticsEvent = $analyticsEventManager->createAnalyticsEventFromDetails($details);

        $this->assertSame($device, $analyticsEvent->getDevice());
        $this->assertSame('Nashville', $analyticsEvent->getGeoCity());
        $this->assertSame('US', $analyticsEvent->getGeoCountryCode());
        $this->assertSame(36.162222, $analyticsEvent->getGeoLatitude());
        $this->assertSame(-86.774444, $analyticsEvent->getGeoLongitude());
        $this->assertSame('TN', $analyticsEvent->getGeoSubdivisionCode());
        $this->assertSame('example.com', $analyticsEvent->getHostname());
        $this->assertSame('anIpAddressAndUserAgentHash', $analyticsEvent->getIpUserAgentHash());
        $this->assertSame('en-GB', $analyticsEvent->getLocale());
        $this->assertSame('a-test-event', $analyticsEvent->getName());
        $this->assertSame('https://example.com/redirect', $analyticsEvent->getRedirectUrl());
        $this->assertSame('https://example.com/referrer', $analyticsEvent->getReferrer());
        $this->assertSame(['foo' => 'bar'], $analyticsEvent->getTags());
        $this->assertSame('https://example.com/', $analyticsEvent->getUri());
        $this->assertSame('MyAgent/123.456.789', $analyticsEvent->getUserAgent());
        $this->assertInstanceOf(DateTimeImmutable::class, $analyticsEvent->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $analyticsEvent->getUpdatedAt());
        $this->assertNull($analyticsEvent->getDeletedAt());
    }
}
