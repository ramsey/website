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

namespace App\Content\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use Psr\Container\ContainerInterface;

class CommonMarkEnvironmentFactory
{
    public function __invoke(ContainerInterface $container): Environment
    {
        /** @var array<string, mixed> $config */
        $config = $container->get('config')['commonmark'] ?? [];

        $environment = new Environment($config);
        $environment->addExtension(new AttributesExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new DescriptionListExtension());
        $environment->addExtension(new FootnoteExtension());
        $environment->addExtension(new FrontMatterExtension());
        $environment->addExtension(new SmartPunctExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());

        return $environment;
    }
}
