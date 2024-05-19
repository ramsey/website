<?php

declare(strict_types=1);

return [
    '/foo/bar' => ['httpStatusCode' => 307, 'redirectUri' => 'https://other.example.com/foo/bar'],
    '/security.txt' => ['httpStatusCode' => 301, 'redirectUri' => '/.well-known/security.txt'],
    '/feeds' => ['httpStatusCode' => 302, 'redirectUri' => 'https://web.archive.org/web/https://benramsey.com/feeds/'],
    '/search' => ['httpStatusCode' => 410],
];
