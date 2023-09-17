<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function catchAll(): Response
    {
        return new Response('<html><body>Whoops!</body></html>', 400);
    }
}
