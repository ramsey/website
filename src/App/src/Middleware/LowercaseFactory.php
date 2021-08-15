<?php

declare(strict_types=1);

namespace App\Middleware;

use Laminas\Diactoros\ResponseFactory;
use Middlewares\Lowercase;

class LowercaseFactory
{
    public function __invoke(): Lowercase
    {
        return (new Lowercase())->redirect(new ResponseFactory());
    }
}
