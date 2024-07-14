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

namespace App\Service\GeoIp2;

use GeoIp2\Model;
use GeoIp2\ProviderInterface;

/**
 * This GeoIp2 provider is no-op, for testing and local development
 */
class NoOpProvider implements ProviderInterface
{
    public function country(string $ipAddress): Model\Country
    {
        return new Model\Country([]);
    }

    public function city(string $ipAddress): Model\City
    {
        return new Model\City([
            'city' => [],
        ]);
    }
}
