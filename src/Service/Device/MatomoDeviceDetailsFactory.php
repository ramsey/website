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

use App\Service\Device\Matomo\DeviceDetectorFactory;
use DeviceDetector\DeviceDetector;

use function strtolower;
use function trim;

/**
 * Uses {@link https://github.com/matomo-org/device-detector matomo/device-detector}
 * to create {@see DeviceDetails}
 *
 * @link http://devicedetector.net Device Detector
 */
final readonly class MatomoDeviceDetailsFactory implements DeviceDetailsFactory
{
    private const string BOT = 'bot';
    private const string LIBRARY = 'library';
    private const string UNK = 'UNK';
    private const string UNKNOWN = 'unknown';

    public function __construct(private DeviceDetectorFactory $deviceDetectorFactory)
    {
    }

    /**
     * @inheritDoc
     */
    public function createFromServerEnvironment(array $serverEnvironment): DeviceDetails
    {
        $detector = $this->deviceDetectorFactory->createFromServerEnvironment($serverEnvironment);
        $detector->parse();

        return new DeviceDetails(
            name: $this->getName($detector),
            type: $this->getDeviceType($detector),
            brandName: $this->getBrandName($detector),
            category: $this->getCategory($detector),
            engine: $this->getEngine($detector),
            family: $this->getFamily($detector),
            isBot: $detector->isBot(),
            osFamily: $this->getOsFamily($detector),
        );
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

        return strtolower($category);
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
