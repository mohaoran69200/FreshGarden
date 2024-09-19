<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository, FavoriteRepository $favoriteRepository): Response
    {
        $products = $productRepository->findAll();
        $user = $this->getUser();

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
