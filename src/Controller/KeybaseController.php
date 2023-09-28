<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function md5;

final class KeybaseController extends AbstractController
{
    #[Route('/.well-known/keybase.txt')]
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
        $response->setPublic();
        $response->isNotModified($request);

        return $response;
    }
}
