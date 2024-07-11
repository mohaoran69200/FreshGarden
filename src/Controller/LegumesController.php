<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegumesController extends AbstractController
{
    #[Route('/légumes', name: 'legumes')]
    public function index(): Response
    {
        return $this->render('legumes/index.html.twig', [
            'controller_name' => 'LegumesController',
        ]);
    }
}
