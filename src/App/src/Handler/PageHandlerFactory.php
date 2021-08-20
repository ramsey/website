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
use Psr\Container\ContainerInterface;

class PageHandlerFactory
{
    public function __invoke(ContainerInterface $container): PageHandler
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = $container->get(PageRepository::class);

        /** @var TemplateRendererInterface $template */
        $template = $container->get(TemplateRendererInterface::class);

        /** @var HtmlResponseFactory $responseFactory */
        $responseFactory = $container->get(HtmlResponseFactory::class);

        return new PageHandler($pageRepository, $template, $responseFactory);
    }
}
