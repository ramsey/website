<?php

declare(strict_types=1);

return (function (): array {
    $benRamseyDev = [
        'aliases' => [],
        'links' => [
            [
                'rel' => 'me',
                'href' => 'https://ben.ramsey.dev',
                'type' => 'text/html',
            ],
            [
                'rel' => 'http://webfinger.net/rel/avatar',
                'href' => 'https://www.gravatar.com/avatar/a0fa77843de8a4a2265bb939180a384b.jpg?s=2000',
                'type' => 'image/png',
            ],
            [
                'rel' => 'http://webfinger.net/rel/profile-page',
                'href' => 'https://ben.ramsey.dev',
                'type' => 'text/html',
            ],
            [
                'rel' => 'self',
                'href' => 'https://phpc.social/users/ramsey',
                'type' => 'application/activity+json',
            ],
        ],
        'properties' => [
            'https://schema.org/name' => 'Ben Ramsey',
            'https://schema.org/email' => 'ben@ramsey.dev',
        ],
        'subject' => 'acct:ben@ramsey.dev',
    ];

    return [
        'ramsey.dev' => [
            'acct:ben@ramsey.dev' => $benRamseyDev,
        ],
        'benramsey.com' => [
            'acct:ben@benramsey.com' => $benRamseyDev,
        ],
        'localhost' => [
            'acct:ben@benramsey.dev' => $benRamseyDev,
        ],
    ];
})();
