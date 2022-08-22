<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog')]
    public function list(): Response
    {
        return $this->render('page.html.twig', [
            'content' => 'List of blog posts',
        ]);
    }

    #[Route('/blog/{year}/{slug}')]
    public function entry(string $slug, int $year): Response
    {
        return $this->render('post.html.twig');
    }
}
