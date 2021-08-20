<?php

/**
 * This file is part of ramsey/website
 *
 * ramsey/website is open source software: you can distribute
 * it and/or modify it under the terms of the MIT License
 * (the "License"). You may not use this file except in
 * compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace App\Repository;

use App\Util\FinderFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\Yaml\Parser;

class AuthorRepositoryFactory
{
    public function __invoke(ContainerInterface $container): AuthorRepository
    {
        /** @var FinderFactory $finderFactory */
        $finderFactory = $container->get(FinderFactory::class);

        /** @var string $authorsPath */
        $authorsPath = $container->get('config')['content']['paths']['authorsPath'] ?? '';

        /** @var Parser $yamlParser */
        $yamlParser = $container->get(Parser::class);

        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        return new AuthorRepository(
            finderFactory: $finderFactory,
            authorsPath: $authorsPath,
            yamlParser: $yamlParser,
            uriFactory: $uriFactory,
        );
    }
}
