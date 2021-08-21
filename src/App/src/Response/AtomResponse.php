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
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Laminas\Diactoros\Response\XmlResponse;
use Psr\Http\Message\StreamInterface;

class AtomResponse extends XmlResponse
{
    use InjectContentTypeTrait;

    /**
     * @param array<string, string|string[]> $headers
     */
    public function __construct(
        StreamInterface | string $xml,
        int $status = StatusCodeInterface::STATUS_OK,
        array $headers = [],
    ) {
        parent::__construct(
            $xml,
            $status,
            $this->injectContentType('application/atom+xml; charset=utf-8', $headers),
        );
    }
}
