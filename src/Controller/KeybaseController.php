<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

use function md5;

final class KeybaseController extends AbstractController
{
    private const MAX_AGE = 60 * 60 * 24 * 7;
    private const STALE = 60 * 60 * 24;

    #[Route('/.well-known/keybase.txt')]
    #[Cache(maxage: self::MAX_AGE, public: true, staleWhileRevalidate: self::STALE)]
    public function handle(Request $request): Response
    {
        $response = new Response();
        $response->headers->add([
            'content-type' => 'text/plain',
        ]);

        $response = match (strtolower($request->getHost())) {
            'ben.ramsey.dev' => $this->render(
                view: 'keybase/ben-ramsey-dev.txt.twig',
                response: $response,
            ),
            'benramsey.com' => $this->render(
                view: 'keybase/benramsey-com.txt.twig',
                response: $response,
            ),
            default => throw $this->createNotFoundException(),
        };

        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
