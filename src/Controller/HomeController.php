<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/')]
    public function home(): Response
    {
        return $this->render('home/home.html.twig');
    }

    #[Route('/test')]
    public function routeTest(): Response
    {
        return new Response('<html><body>Hi, there!</body></html>');
    }
}
