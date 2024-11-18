<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Error\RuntimeError;

class ErrorController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @Route("/error/{code}", name="error")
     */
    public function show(int $code): Response
    {
        $template = 'error/500.html.twig';

        switch ($code) {
            case 404:
                $template = 'error/404.html.twig';
                break;
            case 403:
                $template = 'error/403.html.twig';
                break;
            case 400:
                $template = 'error/400.html.twig';
                break;
            case 401:
                $template = 'error/401.html.twig';
                break;
            case 503:
                $template = 'error/503.html.twig';
                break;
        }

        // Rendre le template avec gestion des exceptions
        try {
            return new Response($this->twig->render($template), $code);
        } catch (LoaderError | SyntaxError | RuntimeError $e) {
            // En cas d'erreur lors du rendu, vous pouvez renvoyer une réponse d'erreur par défaut
            return new Response('Erreur lors du rendu du template : ' . $e->getMessage(), 500);
        }
    }

    /**
     * Gère les exceptions non gérées
     */
    public function exception(HttpExceptionInterface $exception): Response
    {
        $statusCode = $exception->getStatusCode();
        return $this->show($statusCode);
    }
}
