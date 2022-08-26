<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    public function __construct(
        private readonly BlogPostRepository $blogPostRepository,
    ) {
    }

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
        $blogPost = $this->blogPostRepository->findByAttributes([
            'year' => $year,
            'slug' => $slug,
        ]);

        return $this->render('post.html.twig', [
            'post' => $blogPost,
        ]);
    }
}
