<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        /** @var RouterInterface $router */
        $router = $container->get(RouterInterface::class);

        /** @var TemplateRendererInterface|null $template */
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        return new HomePageHandler($container::class, $router, $template);
    }
}
