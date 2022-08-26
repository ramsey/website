<?php

declare(strict_types=1);

namespace App\Controller;

use League\CommonMark\ConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    public function __construct(private readonly ConverterInterface $converter)
    {
    }

    #[Route('/page')]
    public function main(): Response
    {
        $content = <<<'EOD'
            ## Hello, World!

            This is a "markdown" document. Check out https://benramsey.com.

            [TOC]
            EOD;

        return $this->render('page.html.twig', [
            'content' => $this->converter->convert($content),
        ]);
    }
}
