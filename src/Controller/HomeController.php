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
#[Route('/', 'app_home')]
#[Cache(maxage: CacheTtl::Hour->value * 8, public: true, staleWhileRevalidate: CacheTtl::Hour->value * 2)]
final readonly class HomeController
{
    public function __construct(private Environment $twig)
    {
    }

    public function __invoke(Request $request): Response
    {
        $response = new Response($this->twig->render('home.html.twig'));
        $response->setEtag(md5((string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
