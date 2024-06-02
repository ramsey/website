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

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * This script transforms data from a Bitly CSV export into a multidimensional
 * array that may be used in a Doctrine migration.
 *
 * The migration file generated is {@see Version20240602213640}, and I am
 * preserving this script for future reference.
 */

use function Ramsey\Uuid\v7;

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

const PATTERN = '#https?://bram\.se/(.*)#i';

(function (string $bitlyExportPath): void {
    if (file_exists($bitlyExportPath) === false) {
        echo "Could not find Bitly export at $bitlyExportPath\n";
        exit(1);
    }

    $file = fopen($bitlyExportPath, 'r');

    if ($file === false) {
        echo "Could open Bitly export for reading; attempted to read $bitlyExportPath\n";
        exit(3);
    }

    $data = [];

    while ([$link, $customLink, $dateCreated, , $destinationUrl] = fgetcsv($file) ?: []) {
        if (!preg_match(PATTERN, trim($link), $linkMatches)) {
            continue;
        }

        preg_match(PATTERN, trim($customLink), $customLinkMatches);

        $slug = $linkMatches[1] ?? null;
        $customSlug = $customLinkMatches[1] ?? null;
        $created = new DateTimeImmutable(trim($dateCreated));
        $destinationUrl = trim($destinationUrl);

        if ($slug === null) {
            continue;
        }

        $data[] = [
            'id' => v7($created),
            'slug' => $slug,
            'custom_slug' => $customSlug,
            'destination_url' => $destinationUrl,
            'created_at' => $created->format('Y-m-d H:i:s P'),
            'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s P'),
        ];
    }

    $idColumn = array_column($data, 'id');
    $slugColumn = array_column($data, 'custom_slug');

    array_multisort($idColumn, SORT_ASC, $slugColumn, SORT_ASC, $data);

    echo "<?php\n";
    echo "return [\n";

    foreach ($data as $row) {
        echo '    [';
        echo "'id' => '{$row['id']}', ";
        echo "'slug' => '{$row['slug']}', ";
        echo "'custom_slug' => '{$row['custom_slug']}', ";
        echo "'destination_url' => '" . addslashes($row['destination_url']) . "', ";
        echo "'created_at' => '{$row['created_at']}', ";
        echo "'updated_at' => '{$row['updated_at']}'], ";
        echo "'deleted_at' => null";
        echo "],\n";
    }

    echo "];\n";
})($argv[1] ?? '');
