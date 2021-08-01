<?php

declare(strict_types=1);

namespace App\Content\Markdown;

use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\MarkdownConverterInterface;
use Psr\Container\ContainerInterface;

class MarkdownConverterFactory
{
    public function __invoke(ContainerInterface $container): MarkdownConverterInterface
    {
        /** @var EnvironmentInterface $environment */
        $environment = $container->get(EnvironmentInterface::class);

        return new MarkdownConverter($environment);
    }
}
