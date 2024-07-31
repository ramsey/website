<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Entity\ShortUrl;
use App\EventSubscriber\AdminSubscriber;
use App\Service\ShortUrlService;
use DateTimeImmutable;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Event\EntityLifecycleEventInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('AdminSubscriber')]
class AdminSubscriberTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox('returns list of subscribed events mapped to handler methods')]
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                BeforeEntityPersistedEvent::class => ['beforeShortUrlPersisted'],
                BeforeEntityUpdatedEvent::class => ['beforeShortUrlUpdated'],
            ],
            AdminSubscriber::getSubscribedEvents(),
        );
    }

    #[TestDox('does nothing when event passed to beforeShortUrlPersisted() does not have a ShortUrl')]
    public function testBeforeShortUrlPersistedWhenEntityIsNotShortUrl(): void
    {
        $shortUrlManager = Mockery::mock(ShortUrlService::class);
        $shortUrlManager->expects('checkAndSetSlug')->never();

        $event = Mockery::mock(EntityLifecycleEventInterface::class);
        $event->expects('getEntityInstance')->andReturn((object) []);

        $adminSubscriber = new AdminSubscriber($shortUrlManager);
        $adminSubscriber->beforeShortUrlPersisted($event);
    }

    #[TestDox('does nothing when event passed to beforeShortUrlUpdated() does not have a ShortUrl')]
    public function testBeforeShortUrlUpdatedWhenEntityIsNotShortUrl(): void
    {
        $object = Mockery::mock();
        $object->expects('setUpdatedAt')->never(); // @phpstan-ignore-line

        $shortUrlManager = Mockery::mock(ShortUrlService::class);

        $event = Mockery::mock(EntityLifecycleEventInterface::class);
        $event->expects('getEntityInstance')->andReturn($object);

        $adminSubscriber = new AdminSubscriber($shortUrlManager);
        $adminSubscriber->beforeShortUrlUpdated($event);
    }

    #[TestDox('calls checkAndSetSlug on the short URL manager when beforeShortUrlPersisted() invoked')]
    public function testBeforeShortUrlPersisted(): void
    {
        $shortUrl = new ShortUrl();

        $shortUrlManager = Mockery::mock(ShortUrlService::class);
        $shortUrlManager->expects('checkAndSetSlug')->with($shortUrl);

        $event = Mockery::mock(EntityLifecycleEventInterface::class);
        $event->expects('getEntityInstance')->andReturn($shortUrl);

        $adminSubscriber = new AdminSubscriber($shortUrlManager);
        $adminSubscriber->beforeShortUrlPersisted($event);
    }

    #[TestDox('calls setUpdatedAt on the short URL when beforeShortUrlUpdated() invoked')]
    public function testBeforeShortUrlUpdated(): void
    {
        $shortUrl = Mockery::mock(ShortUrl::class);
        $shortUrl->expects('setUpdatedAt')->with(Mockery::type(DateTimeImmutable::class));

        $shortUrlManager = Mockery::mock(ShortUrlService::class);

        $event = Mockery::mock(EntityLifecycleEventInterface::class);
        $event->expects('getEntityInstance')->andReturn($shortUrl);

        $adminSubscriber = new AdminSubscriber($shortUrlManager);
        $adminSubscriber->beforeShortUrlUpdated($event);
    }
}
