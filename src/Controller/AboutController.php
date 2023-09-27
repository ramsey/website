<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AboutController extends AbstractController
{
    #[Route('/about')]
    public function about(): Response
    {
        return $this->render('about/about.html.twig');
    }

    #[Route('/copyright')]
    public function copyright(): Response
    {
        return $this->render('about/copyright.html.twig');
    }
}
