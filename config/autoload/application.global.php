<?php

/**
 * Global application configuration settings
 */

declare(strict_types=1);

return [
    'content' => [
        'defaultAuthors' => [
            'ramsey',
        ],
        'paths' => [
            'pagesPath' => __DIR__ . '/../../resources/content/pages',
            'postsPath' => __DIR__ . '/../../resources/content/posts',
            'authorsPath' => __DIR__ . '/../../resources/content/authors',
        ],
    ],
    'commonmark' => [
    ],
];
