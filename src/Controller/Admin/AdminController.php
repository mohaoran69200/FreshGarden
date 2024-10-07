<?php

namespace App\Controller\Admin;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'app_admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: '_dashboard')]
    #[isGranted('ROLE_ADMIN')]
    public function dashboard(UserRepository $userRepository, ProductRepository $productRepository): Response
    {
        //Je récupére le nombre total d'utilisateur
        $totalUsers = $userRepository->count([]);

        //Je récupére le nombre total d'annonces
        $totalProducts = $productRepository->count([]);

        return $this->render('admin/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalProducts' => $totalProducts,
        ]);
    }
}
