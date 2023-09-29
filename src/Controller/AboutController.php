<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

final class AboutController extends AbstractController
{
    private const MAX_AGE = 60 * 60 * 24 * 7;
    private const STALE = 60 * 60 * 24;

    #[Route('/about')]
    #[Cache(maxage: self::MAX_AGE, public: true, staleWhileRevalidate: self::STALE)]
    public function about(Request $request): Response
    {
        $response = $this->render('about/about.html.twig');
        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    #[Route('/copyright')]
    #[Cache(maxage: self::MAX_AGE, public: true, staleWhileRevalidate: self::STALE)]
    public function copyright(Request $request): Response
    {
        $response = $this->render('about/copyright.html.twig');
        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
