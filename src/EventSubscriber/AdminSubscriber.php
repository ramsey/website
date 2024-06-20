<?php

/**
 * This file is part of ramsey/website
 *
 * Copyright (c) Ben Ramsey <ben@ramsey.dev>
 *
 * ramsey/website is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * ramsey/website is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ramsey/website. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\ShortUrl;
use App\Entity\User;
use App\Service\ShortUrlService;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Event\EntityLifecycleEventInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function assert;

final readonly class AdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ShortUrlService $shortUrlManager,
        private Security $security,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['beforeShortUrlSaved'],
            BeforeEntityUpdatedEvent::class => ['beforeShortUrlSaved'],
        ];
    }

    public function beforeShortUrlSaved(EntityLifecycleEventInterface $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof ShortUrl) {
            return;
        }

        $user = $this->security->getUser();
        assert($user instanceof User);

        $this->shortUrlManager->updateShortUrl($entity, $user);
    }
}
