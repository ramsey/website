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

use App\Repository\PostRepository;
use App\Response\HtmlResponseFactory;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BlogHandler implements RequestHandlerInterface
{
    public function __construct(
        private PostRepository $repository,
        private TemplateRendererInterface $renderer,
        private HtmlResponseFactory $responseFactory,
        private RouterInterface $router,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $attributes = [
            'year' => (int) $request->getAttribute('year'),
            'slug' => (string) $request->getAttribute('slug'),
        ];

        $month = $request->getAttribute('month');

        if ($month !== null) {
            $attributes['month'] = (int) $month;
        }

        $blogPost = $this->repository->findByAttributes($attributes);

        if ($blogPost === null) {
            return $this->responseFactory->notFound();
        }

        // If month is part of the URL, respond with a permanent redirect
        // to the new URL format: /blog/YYYY/slug
        if ($month !== null) {
            return $this->responseFactory->redirect(
                uri: $request->getUri()->withPath($this->router->generateUri('blog.post', $attributes)),
                status: StatusCodeInterface::STATUS_MOVED_PERMANENTLY,
            );
        }

        return $this->responseFactory->response($this->renderer->render(
            'app::blog',
            [
                'title' => $blogPost->getTitle(),
                'content' => $blogPost->getContent(),
            ],
        ));
    }
}
