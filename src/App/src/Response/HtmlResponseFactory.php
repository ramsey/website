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

namespace App\Response;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class HtmlResponseFactory
{
    public function __construct(
        private TemplateRendererInterface $template,
    ) {
    }

    /**
     * @param array<string, string|string[]> $headers
     */
    public function response(
        StreamInterface | string $content,
        int $status = StatusCodeInterface::STATUS_OK,
        array $headers = []
    ): ResponseInterface {
        return new HtmlResponse(html: $content, status: $status, headers: $headers);
    }

    /**
     * @param array<string, string|string[]> $headers
     */
    public function redirect(
        UriInterface | string $uri,
        int $status = StatusCodeInterface::STATUS_FOUND,
        array $headers = []
    ): ResponseInterface {
        return new RedirectResponse(uri: $uri, status: $status, headers: $headers);
    }

    /**
     * @param array<string, string|string[]> $headers
     */
    public function notFound(array $headers = []): ResponseInterface
    {
        return $this->response(
            content: $this->template->render('error::404'),
            status: StatusCodeInterface::STATUS_NOT_FOUND,
            headers: $headers,
        );
    }
}
