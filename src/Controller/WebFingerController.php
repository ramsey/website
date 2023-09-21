<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebFingerController extends AbstractController
{
    #[Route('/.well-known/webfinger')]
    public function handle(): Response
    {
        return new Response('{}', 200, ['content-type' => 'application/json']);
    }
}
