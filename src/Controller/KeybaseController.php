<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\CacheTtl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

use function md5;
use function strtolower;

#[AsController]
#[Route('/.well-known/keybase.txt')]
#[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
final readonly class KeybaseController
{
    public function __construct(private Environment $twig)
    {
    }

    public function __invoke(Request $request): Response
    {
        $content = match (strtolower($request->getHost())) {
            'ben.ramsey.dev' => $this->twig->render('keybase/ben-ramsey-dev.txt.twig'),
            'benramsey.com' => $this->twig->render('keybase/benramsey-com.txt.twig'),
            default => throw new NotFoundHttpException(),
        };

        $response = new Response($content);
        $response->headers->add(['content-type' => 'text/plain']);
        $response->setEtag(md5((string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
