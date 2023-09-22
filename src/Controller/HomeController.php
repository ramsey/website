<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/')]
    public function home(): Response
    {
        return $this->render('home/home.html.twig');
    }
}
