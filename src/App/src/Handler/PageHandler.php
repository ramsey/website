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

use App\Repository\PageRepository;
use App\Response\HtmlResponseFactory;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageHandler implements RequestHandlerInterface
{
    public function __construct(
        private PageRepository $repository,
        private TemplateRendererInterface $renderer,
        private HtmlResponseFactory $responseFactory,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $attributes = [
            'slug' => (string) $request->getAttribute('slug'),
        ];

        $page = $this->repository->findByAttributes($attributes);

        if ($page === null) {
            return $this->responseFactory->notFound();
        }

        return $this->responseFactory->response($this->renderer->render(
            'app::page',
            [
                'title' => $page->getTitle(),
                'content' => $page->getContent(),
            ],
        ));
    }
}
