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
        $blogPosts = $this->blogPostRepository->findAll();

        return $this->render('post/list.html.twig', [
            'posts' => $blogPosts,
        ]);
    }

    #[Route('/blog/{year}/{slug}', name: 'blog_post')]
    public function entry(string $slug, int $year): Response
    {
        $blogPost = $this->blogPostRepository->findByAttributes([
            'year' => $year,
            'slug' => $slug,
        ]);

        $layout = 'post.html.twig';
        if (isset($blogPost->metadata['image'])) {
            $layout = 'post/split-with-image.html.twig';
        }

        return $this->render($layout, [
            'post' => $blogPost,
        ]);
    }
}
