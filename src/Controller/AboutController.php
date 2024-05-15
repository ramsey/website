<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\CacheTtl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

use function md5;

#[AsController]
#[Route('/about', 'app_about')]
#[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
final readonly class AboutController
{
    public function __construct(private Environment $twig)
    {
    }

    public function __invoke(Request $request): Response
    {
        $content = $this->twig->render('about.html.twig');

        $response = new Response($content);
        $response->setEtag(md5($content));
        $response->isNotModified($request);

        return $response;
    }
}
