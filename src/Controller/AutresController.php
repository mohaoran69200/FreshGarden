<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AutresController extends AbstractController
{
    #[Route('/autres', name: 'autres')]
    public function index(): Response
    {
        return $this->render('autres/index.html.twig', [
            'controller_name' => 'AutresController',
        ]);
    }
}
