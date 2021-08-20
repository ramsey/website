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

namespace App\Handler\Blog;

use App\Repository\PostRepository;
use App\Response\HtmlResponseFactory;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class ListHandlerFactory
{
    public function __invoke(ContainerInterface $container): ListHandler
    {
        /** @var PostRepository $postRepository */
        $postRepository = $container->get(PostRepository::class);

        /** @var TemplateRendererInterface $template */
        $template = $container->get(TemplateRendererInterface::class);

        /** @var HtmlResponseFactory $responseFactory */
        $responseFactory = $container->get(HtmlResponseFactory::class);

        /** @var RouterInterface $router */
        $router = $container->get(RouterInterface::class);

        return new ListHandler($postRepository, $template, $responseFactory, $router);
    }
}
