<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository, FavoriteRepository $favoriteRepository, SessionInterface $session): Response
    {
        $products = $productRepository->findAll();
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN') && $session->get('login_origin')) {
                $session->remove('login_origin');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        $favorites = [];
        if ($user) {
            $favorites = $favoriteRepository->findBy(['user' => $user]);
        }

        $favoritesMap = [];
        foreach ($favorites as $favorite) {
            $productFavorite = $favorite->getProductFavorite();
            $userFavorite = $favorite->getUserFavorite();

            // Vérifie si le favori a un produit et un utilisateur associés
            if ($productFavorite !== null && $userFavorite !== null) {
                $favoritesMap[$productFavorite->getId()] = true;
            }
        }

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'favorites' => $favoritesMap,  // Passez les favoris comme tableau associatif
        ]);
    }
}
