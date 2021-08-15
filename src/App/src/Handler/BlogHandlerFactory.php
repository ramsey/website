<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\PostRepository;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class BlogHandlerFactory
{
    public function __invoke(ContainerInterface $container): BlogHandler
    {
        /** @var PostRepository $postRepository */
        $postRepository = $container->get(PostRepository::class);

        /** @var TemplateRendererInterface $template */
        $template = $container->get(TemplateRendererInterface::class);

        return new BlogHandler($postRepository, $template);
    }
}
