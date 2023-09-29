<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    private const MAX_AGE = 60 * 60 * 8;
    private const STALE = 60 * 60 * 2;

    #[Route('/')]
    #[Cache(maxage: self::MAX_AGE, public: true, staleWhileRevalidate: self::STALE)]
    public function home(Request $request): Response
    {
        $response = $this->render('home/home.html.twig');
        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
