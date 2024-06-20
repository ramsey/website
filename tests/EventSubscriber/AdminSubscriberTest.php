<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Entity\ShortUrl;
use App\Entity\User;
use App\EventSubscriber\AdminSubscriber;
use App\Service\ShortUrlService;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Event\EntityLifecycleEventInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

#[TestDox('AdminSubscriber')]
class AdminSubscriberTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox('returns list of subscribed events mapped to handler methods')]
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                BeforeEntityPersistedEvent::class => ['beforeShortUrlSaved'],
                BeforeEntityUpdatedEvent::class => ['beforeShortUrlSaved'],
            ],
            AdminSubscriber::getSubscribedEvents(),
        );
    }

    #[TestDox('does nothing when event passed to beforeShortUrlSaved() does not have a ShortUrl')]
    public function testBeforeShortUrlSavedWhenEntityIsNotShortUrl(): void
    {
        $shortUrlManager = Mockery::mock(ShortUrlService::class);
        $security = Mockery::mock(Security::class);
        $event = Mockery::mock(EntityLifecycleEventInterface::class);

        $event->expects('getEntityInstance')->andReturn((object) []);
        $security->expects('getUser')->never();
        $shortUrlManager->expects('updateShortUrl')->never();

        $adminSubscriber = new AdminSubscriber($shortUrlManager, $security);
        $adminSubscriber->beforeShortUrlSaved($event);
    }

    #[TestDox('calls updateShortUrl on the short URL manager when beforeShortUrlSaved() invoked')]
    public function testBeforeShortUrlSaved(): void
    {
        $shortUrlManager = Mockery::mock(ShortUrlService::class);
        $security = Mockery::mock(Security::class);
        $event = Mockery::mock(EntityLifecycleEventInterface::class);

        $user = new User();
        $shortUrl = new ShortUrl();

        $event->expects('getEntityInstance')->andReturn($shortUrl);
        $security->expects('getUser')->andReturn($user);
        $shortUrlManager->expects('updateShortUrl')->with($shortUrl, $user);

        $adminSubscriber = new AdminSubscriber($shortUrlManager, $security);
        $adminSubscriber->beforeShortUrlSaved($event);
    }
}
