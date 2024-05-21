<?php

declare(strict_types=1);

return [
    'one.example.com' => [
        'acct:frodo@one.example.com' => [
            'aliases' => [
                'acct:fbaggins@one.example.com',
            ],
            'links' => [
                [
                    'rel' => 'me',
                    'href' => 'https://frodo.one.example.com',
                    'type' => 'text/html',
                ],
                [
                    'rel' => 'http://webfinger.net/rel/profile-page',
                    'href' => 'https://frodo.one.example.com',
                    'type' => 'text/html',
                ],
            ],
            'properties' => [
                'https://schema.org/name' => 'Frodo Baggins',
                'https://schema.org/email' => 'frodo@one.example.com',
            ],
            'subject' => 'acct:frodo@one.example.com',
        ],
        'acct:samwise@one.example.com' => [
            'links' => [
                [
                    'rel' => 'me',
                    'href' => 'https://samwise.one.example.com',
                    'type' => 'text/html',
                ],
            ],
            'subject' => 'acct:samwise@one.example.com',
        ],
    ],
    'two.example.com' => [
        'acct:pippin@two.example.com' => [
            'subject' => 'acct:pippin@two.example.com',
        ],
    ],
];
