<?php

declare(strict_types=1);

namespace App\Response;

use Laminas\Diactoros\Response;

class NotFoundResponse extends Response
{
    public function __construct()
    {
        parent::__construct(status: 404);
    }
}
