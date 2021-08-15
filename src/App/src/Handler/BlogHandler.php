<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\PostRepository;
use App\Response\NotFoundResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BlogHandler implements RequestHandlerInterface
{
    public function __construct(
        private PostRepository $repository,
        private TemplateRendererInterface $renderer,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $blogPost = $this->repository->find(
            (int) $request->getAttribute('year'),
            (int) $request->getAttribute('month'),
            (string) $request->getAttribute('slug'),
        );

        if ($blogPost === null) {
            return new NotFoundResponse();
        }

        return new HtmlResponse($this->renderer->render(
            'app::blog',
            [
                'title' => $blogPost->getTitle(),
                'content' => $blogPost->getContent(),
            ],
        ));
    }
}
