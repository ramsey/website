<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\AnalyticsDevice;
use App\Entity\AnalyticsEvent;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[TestDox('Analytics event entity')]
final class AnalyticsEventTest extends KernelTestCase
{
    /**
     * @param array<string, string> | float | object | string | null $value
     */
    #[TestDox('sets and gets')]
    #[TestWith(['setDevice', 'getDevice', new AnalyticsDevice()])]
    #[TestWith(['setGeoCity', 'getGeoCity', 'City Name'])]
    #[TestWith(['setGeoCity', 'getGeoCity', null])]
    #[TestWith(['setGeoCountryCode', 'getGeoCountryCode', 'XX'])]
    #[TestWith(['setGeoCountryCode', 'getGeoCountryCode', null])]
    #[TestWith(['setGeoLatitude', 'getGeoLatitude', -10.185722])]
    #[TestWith(['setGeoLatitude', 'getGeoLatitude', null])]
    #[TestWith(['setGeoLongitude', 'getGeoLongitude', -76.863155])]
    #[TestWith(['setGeoLongitude', 'getGeoLongitude', null])]
    #[TestWith(['setGeoSubdivisionCode', 'getGeoSubdivisionCode', 'XXX'])]
    #[TestWith(['setGeoSubdivisionCode', 'getGeoSubdivisionCode', null])]
    #[TestWith(['setHostname', 'getHostname', 'example.com'])]
    #[TestWith(['setIpUserAgentHash', 'getIpUserAgentHash', '0123456789abcdef'])]
    #[TestWith(['setLocale', 'getLocale', 'en-US'])]
    #[TestWith(['setLocale', 'getLocale', null])]
    #[TestWith(['setName', 'getName', 'pageview'])]
    #[TestWith(['setRedirectUrl', 'getRedirectUrl', 'https://example.com/redirect'])]
    #[TestWith(['setRedirectUrl', 'getRedirectUrl', null])]
    #[TestWith(['setReferrer', 'getReferrer', 'https://example.com/referrer'])]
    #[TestWith(['setReferrer', 'getReferrer', null])]
    #[TestWith(['setTags', 'getTags', ['tag1' => 'value1', 'tag2' => 'value2']])]
    #[TestWith(['setTags', 'getTags', []])]
    #[TestWith(['setUri', 'getUri', 'https://example.com/path/to/content'])]
    #[TestWith(['setUserAgent', 'getUserAgent', 'Foo/1.2'])]
    public function testSetAndGet(string $setter, string $getter, array | float | object | string | null $value): void
    {
        $event = new AnalyticsEvent();

        $this->assertSame($event, $event->{$setter}($value));
        $this->assertSame($value, $event->{$getter}());
    }

    #[TestDox('sets the createdAt property')]
    public function testSetCreatedAt(): void
    {
        $date = new DateTime();

        $event = new AnalyticsEvent();

        $this->assertSame($event, $event->setCreatedAt($date));
        $this->assertNotSame($date, $event->getCreatedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $event->getCreatedAt());
        $this->assertSame($date->format('c'), $event->getCreatedAt()->format('c'));
    }

    #[TestDox('sets the updatedAt property')]
    public function testSetUpdatedAt(): void
    {
        $date = new DateTime();

        $event = new AnalyticsEvent();

        $this->assertSame($event, $event->setUpdatedAt($date));
        $this->assertNotSame($date, $event->getUpdatedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $event->getUpdatedAt());
        $this->assertSame($date->format('c'), $event->getUpdatedAt()->format('c'));
    }

    #[TestDox('sets the deletedAt property')]
    public function testSetDeletedAt(): void
    {
        $date = new DateTime();

        $event = new AnalyticsEvent();

        $this->assertSame($event, $event->setDeletedAt($date));
        $this->assertNotSame($date, $event->getDeletedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $event->getDeletedAt());
        $this->assertSame($date->format('c'), $event->getDeletedAt()->format('c'));
    }
}
