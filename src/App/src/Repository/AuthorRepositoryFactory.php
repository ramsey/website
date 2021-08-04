<?php

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

        return new AuthorRepository($finderFactory, $authorsPath, $yamlParser, $uriFactory);
    }
}
