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
