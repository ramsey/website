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

namespace App;

use App\Repository\AuthorRepository;
use App\Repository\AuthorRepositoryFactory;
use App\Repository\PageRepository;
use App\Repository\PageRepositoryFactory;
use App\Repository\PostRepository;
use App\Repository\PostRepositoryFactory;
use App\Response\HtmlResponseFactory;
use App\Response\HtmlResponseFactoryFactory;
use App\Response\XmlResponseFactory;
use App\Util\FinderFactory;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\MarkdownConverterInterface;
use Middlewares\Lowercase;
use Middlewares\TrailingSlash;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array{
     *     dependencies: array{
     *         invokables: array<class-string, class-string|callable>,
     *         factories: array<class-string, class-string|callable>
     *     },
     *     templates: array{paths: array<string, string[]>}
     * }
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array{
     *     invokables: array<class-string, class-string|callable>,
     *     factories: array<class-string, class-string|callable>
     * }
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                FinderFactory::class => FinderFactory::class,
                XmlResponseFactory::class => XmlResponseFactory::class,
            ],
            'factories' => [
                AuthorRepository::class => AuthorRepositoryFactory::class,
                EnvironmentInterface::class => Content\Markdown\CommonMarkEnvironmentFactory::class,
                Handler\Blog\FeedHandler::class => Handler\Blog\FeedHandlerFactory::class,
                Handler\Blog\ListHandler::class => Handler\Blog\ListHandlerFactory::class,
                Handler\Blog\PostHandler::class => Handler\Blog\PostHandlerFactory::class,
                Handler\HomeHandler::class => Handler\HomeHandlerFactory::class,
                Handler\PageHandler::class => Handler\PageHandlerFactory::class,
                HtmlResponseFactory::class => HtmlResponseFactoryFactory::class,
                Lowercase::class => Middleware\LowercaseFactory::class,
                MarkdownConverterInterface::class => Content\Markdown\MarkdownConverterFactory::class,
                PageRepository::class => PageRepositoryFactory::class,
                PostRepository::class => PostRepositoryFactory::class,
                TrailingSlash::class => Middleware\TrailingSlashFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array{paths: array<string, string[]>}
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app' => [__DIR__ . '/../templates/app'],
                'error' => [__DIR__ . '/../templates/error'],
                'layout' => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }
}
