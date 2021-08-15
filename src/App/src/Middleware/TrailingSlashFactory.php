<?php

declare(strict_types=1);

namespace App\Middleware;

use Laminas\Diactoros\ResponseFactory;
use Middlewares\TrailingSlash;

class TrailingSlashFactory
{
    public function __invoke(): TrailingSlash
    {
        return (new TrailingSlash(true))->redirect(new ResponseFactory());
    }
}
