<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/page')]
    public function main(): Response
    {
        return $this->render('page.html.twig', [
            'content' => 'Hello!',
        ]);
    }
}
