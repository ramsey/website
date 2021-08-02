<?php

declare(strict_types=1);

namespace App\Repository;

use App\Util\FinderFactory;
use League\CommonMark\MarkdownConverterInterface;
use Psr\Container\ContainerInterface;

class PostRepositoryFactory
{
    public function __invoke(ContainerInterface $container): PostRepository
    {
        /** @var FinderFactory $finderFactory */
        $finderFactory = $container->get(FinderFactory::class);

        /** @var string $postsPath */
        $postsPath = $container->get('config')['postsPath'] ?? '';

        /** @var MarkdownConverterInterface $markdownConverter */
        $markdownConverter = $container->get(MarkdownConverterInterface::class);

        return new PostRepository($finderFactory, $postsPath, $markdownConverter);
    }
}
