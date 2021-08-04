<?php

declare(strict_types=1);

namespace App;

use App\Repository\AuthorRepository;
use App\Repository\AuthorRepositoryFactory;
use App\Repository\PostRepository;
use App\Repository\PostRepositoryFactory;
use App\Util\FinderFactory;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\MarkdownConverterInterface;

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
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'factories' => [
                AuthorRepository::class => AuthorRepositoryFactory::class,
                EnvironmentInterface::class => Content\Markdown\CommonMarkEnvironmentFactory::class,
                Handler\HomePageHandler::class => Handler\HomePageHandlerFactory::class,
                MarkdownConverterInterface::class => Content\Markdown\MarkdownConverterFactory::class,
                PostRepository::class => PostRepositoryFactory::class,
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
