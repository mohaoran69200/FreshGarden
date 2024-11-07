<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/information', name: 'app_information_')]
class InformationController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('information/contact.html.twig');
    }

    #[Route('/conditions-generales-utilisation', name: 'cgu')]
    public function cgu(): Response
    {
        return $this->render('information/cgu.html.twig');
    }

    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('information/mentions_legales.html.twig');
    }

    #[Route('/conditions-generales-ventes', name: 'cgv')]
    public function cgv(): Response
    {
        return $this->render('information/cgv.html.twig');
    }

    #[Route('/a-propos', name: 'a_propos')]
    public function aPropos(): Response
    {
        return $this->render('information/a_propos.html.twig');
    }
}
