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

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use Laminas\Diactoros\UriFactory;
use Psr\Http\Message\UriInterface;
use Throwable;

use function is_string;

final class UrlType extends Type
{
    private const string NAME = 'url';

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 255]);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof UriInterface) {
            return (string) $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw SerializationFailed::new(
                $value,
                'string',
                'Value must be a string or instance of ' . UriInterface::class,
            );
        }

        $factory = new UriFactory();

        try {
            return (string) $factory->createUri($value);
        } catch (Throwable $throwable) {
            throw SerializationFailed::new($value, 'string', 'Unable to parse value as a URI.', $throwable);
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UriInterface
    {
        if ($value instanceof UriInterface) {
            return $value;
        }

        if (!is_string($value) || $value === '') {
            return null;
        }

        $factory = new UriFactory();

        try {
            return $factory->createUri($value);
        } catch (Throwable) {
            throw ValueNotConvertible::new($value, self::NAME);
        }
    }
}
