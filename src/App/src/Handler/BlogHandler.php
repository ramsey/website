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
use App\Response\NotFoundResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BlogHandler implements RequestHandlerInterface
{
    public function __construct(
        private PostRepository $repository,
        private TemplateRendererInterface $renderer,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $blogPost = $this->repository->find(
            (int) $request->getAttribute('year'),
            (int) $request->getAttribute('month'),
            (string) $request->getAttribute('slug'),
        );

        if ($blogPost === null) {
            return new NotFoundResponse();
        }

        return new HtmlResponse($this->renderer->render(
            'app::blog',
            [
                'title' => $blogPost->getTitle(),
                'content' => $blogPost->getContent(),
            ],
        ));
    }
}
