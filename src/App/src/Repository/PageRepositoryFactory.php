<?php

declare(strict_types=1);

namespace App\Repository;

use App\Util\FinderFactory;
use League\CommonMark\MarkdownConverterInterface;
use Psr\Container\ContainerInterface;

class PageRepositoryFactory
{
    public function __invoke(ContainerInterface $container): PageRepository
    {
        /** @var FinderFactory $finderFactory */
        $finderFactory = $container->get(FinderFactory::class);

        /** @var string $postsPath */
        $postsPath = $container->get('config')['content']['paths']['pagesPath'] ?? '';

        /** @var MarkdownConverterInterface $markdownConverter */
        $markdownConverter = $container->get(MarkdownConverterInterface::class);

        return new PageRepository($finderFactory, $postsPath, $markdownConverter);
    }
}
