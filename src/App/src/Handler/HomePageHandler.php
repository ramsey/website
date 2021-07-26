<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomePageHandler implements RequestHandlerInterface
{
    private string $containerName;

    private Router\RouterInterface $router;

    private ?TemplateRendererInterface $template = null;

    public function __construct(
        string $containerName,
        Router\RouterInterface $router,
        ?TemplateRendererInterface $template = null
    ) {
        $this->containerName = $containerName;
        $this->router = $router;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];

        $data['containerName'] = 'Laminas Servicemanager';
        $data['containerDocs'] = 'https://docs.laminas.dev/laminas-servicemanager/';

        $data['routerName'] = 'Laminas Router';
        $data['routerDocs'] = 'https://docs.laminas.dev/laminas-router/';

        $data['templateName'] = 'Twig';
        $data['templateDocs'] = 'https://twig.sensiolabs.org/documentation';

        return new HtmlResponse((string) $this->template?->render('app::home-page', $data));
    }
}
