<?php

declare(strict_types=1);

namespace App\Service;

use League\CommonMark\ConverterInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;

class CommonMark implements ConverterInterface
{
    private readonly ConverterInterface $converter;

    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(array $configuration = [])
    {
        $environment = (new Environment($configuration))
            ->addExtension(new AttributesExtension())
            ->addExtension(new AutolinkExtension())
            ->addExtension(new CommonMarkCoreExtension())
            ->addExtension(new DescriptionListExtension())
            ->addExtension(new FootnoteExtension())
            ->addExtension(new FrontMatterExtension())
            ->addExtension(new HeadingPermalinkExtension())
            ->addExtension(new SmartPunctExtension())
            ->addExtension(new StrikethroughExtension())
            ->addExtension(new TableExtension())
            ->addExtension(new TableOfContentsExtension())
            ->addExtension(new TaskListExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    public function convert(string $input): RenderedContentInterface
    {
        return $this->converter->convert($input);
    }
}
