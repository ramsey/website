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

namespace App\Service;

use App\Entity\AnalyticsDevice;
use App\Repository\AnalyticsDeviceRepository;
use App\Service\Device\DeviceDetailsFactory;
use DateTimeImmutable;

/**
 * Manages creation of analytics devices
 */
final readonly class AnalyticsDeviceManager implements AnalyticsDeviceService
{
    public function __construct(
        private AnalyticsDeviceRepository $repository,
        private DeviceDetailsFactory $deviceDetailsFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDevice(array $serverEnvironment): AnalyticsDevice
    {
        $details = $this->deviceDetailsFactory->createFromServerEnvironment($serverEnvironment);

        $device = $this->repository->findOneBy([
            'brandName' => $details->brandName,
            'category' => $details->category,
            'device' => $details->type,
            'engine' => $details->engine,
            'family' => $details->family,
            'name' => $details->name,
            'osFamily' => $details->osFamily,
        ]);

        // If we already have a matching device in the database, use it.
        if ($device instanceof AnalyticsDevice) {
            return $device;
        }

        // If we didn't find a matching device, create a new one.
        return (new AnalyticsDevice())
            ->setBrandName($details->brandName)
            ->setCategory($details->category)
            ->setDevice($details->type)
            ->setEngine($details->engine)
            ->setFamily($details->family)
            ->setBot($details->isBot)
            ->setName($details->name)
            ->setOsFamily($details->osFamily)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
    }
}
