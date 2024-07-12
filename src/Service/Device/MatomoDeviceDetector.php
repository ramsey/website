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

namespace App\Service\Device;

use App\Entity\AnalyticsDevice;
use App\Repository\AnalyticsDeviceRepository;
use DateTimeImmutable;
use DeviceDetector\DeviceDetector;

use function trim;

/**
 * Uses {@link https://github.com/matomo-org/device-detector matomo/device-detector}
 * to fetch or create an {@see AnalyticsDevice}
 *
 * @link http://devicedetector.net Device Detector
 */
final readonly class MatomoDeviceDetector implements DeviceService
{
    private const string BOT = 'bot';
    private const string LIBRARY = 'library';
    private const string UNK = 'UNK';
    private const string UNKNOWN = 'unknown';

    public function __construct(
        private AnalyticsDeviceRepository $repository,
        private DeviceDetectorFactory $deviceDetectorFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDevice(string $userAgent, array $server): AnalyticsDevice
    {
        $detector = $this->deviceDetectorFactory->createFromServerEnvironment($server);
        $detector->parse();

        $properties = [
            'brandName' => $this->getBrandName($detector),
            'category' => $this->getCategory($detector),
            'device' => $this->getDeviceType($detector),
            'engine' => $this->getEngine($detector),
            'family' => $this->getFamily($detector),
            'name' => $this->getName($detector),
            'osFamily' => $this->getOsFamily($detector),
        ];

        // If we already have a matching device in the database, use it.
        $device = $this->repository->findOneBy($properties);
        if ($device instanceof AnalyticsDevice) {
            return $device;
        }

        // If we didn't find a matching device, create a new one.
        return (new AnalyticsDevice())
            ->setBrandName($properties['brandName'])
            ->setCategory($properties['category'])
            ->setDevice($properties['device'])
            ->setEngine($properties['engine'])
            ->setFamily($properties['family'])
            ->setBot($detector->isBot())
            ->setName($properties['name'])
            ->setOsFamily($properties['osFamily'])
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
    }

    private function getBrandName(DeviceDetector $detector): ?string
    {
        /** @var string $brandName */
        $brandName = $detector->getBot()['producer']['name'] ?? $detector->getBrandName();
        $brandName = trim($brandName);

        if ($brandName === self::UNK || $brandName === '') {
            return null;
        }

        return $brandName;
    }

    private function getCategory(DeviceDetector $detector): ?string
    {
        /** @var string $category */
        $category = $detector->getBot()['category'] ?? $detector->getClient('type') ?? '';
        $category = trim($category);

        if ($category === self::UNK || $category === '') {
            return null;
        }

        return $category;
    }

    private function getDeviceType(DeviceDetector $detector): string
    {
        if ($detector->isBot()) {
            return self::BOT;
        }

        if ($detector->getDeviceName() === self::UNK || $detector->getDeviceName() === '') {
            if ($this->getCategory($detector) === self::LIBRARY) {
                return self::LIBRARY;
            }

            return self::UNKNOWN;
        }

        return $detector->getDeviceName();
    }

    private function getEngine(DeviceDetector $detector): ?string
    {
        /** @var string $engine */
        $engine = $detector->getClient('engine') ?? '';
        $engine = trim($engine);

        if ($engine === self::UNK || $engine === '') {
            return null;
        }

        return $engine;
    }

    private function getFamily(DeviceDetector $detector): ?string
    {
        /** @var string $family */
        $family = $detector->getClient('family') ?? '';
        $family = trim($family);

        if ($family === self::UNK || $family === '') {
            return null;
        }

        return $family;
    }

    private function getName(DeviceDetector $detector): string
    {
        /** @var string $name */
        $name = $detector->getBot()['name'] ?? $detector->getClient('name') ?? '';
        $name = trim($name);

        if ($name === self::UNK || $name === '') {
            return self::UNKNOWN;
        }

        return $name;
    }

    private function getOsFamily(DeviceDetector $detector): ?string
    {
        /** @var string $osFamily */
        $osFamily = $detector->getOs('family') ?? '';
        $osFamily = trim($osFamily);

        if ($osFamily === self::UNK || $osFamily === '') {
            return null;
        }

        return $osFamily;
    }
}
