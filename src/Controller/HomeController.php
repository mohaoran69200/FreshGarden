<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // J'affiche la page d'accueil avec la liste des produits et les favoris de l'utilisateur connecté.
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository, FavoriteRepository $favoriteRepository): Response
    {
        // Je récupère tous les produits pour les afficher sur la page d'accueil.
        $products = $productRepository->findAll();

        // Je récupère l'utilisateur connecté.
        $user = $this->getUser();

        $favorites = [];
        // Si l'utilisateur est connecté, je récupère ses favoris.
        if ($user) {
            $favorites = $favoriteRepository->findBy(['user' => $user]);
        }

        $favoritesMap = [];
        // Je parcours les favoris de l'utilisateur pour construire un tableau d'identifiants de produits favoris.
        foreach ($favorites as $favorite) {
            $productFavorite = $favorite->getProductFavorite();
            $userFavorite = $favorite->getUserFavorite();

            // Si l'élément favori est à la fois un produit et un utilisateur (pas probable dans ce cas, mais vérifié),
            // je marque le produit comme favori dans le tableau $favoritesMap.
            if ($productFavorite !== null && $userFavorite !== null) {
                $favoritesMap[$productFavorite->getId()] = true;
            }
        }

        // Je retourne la vue avec la liste des produits et la carte des favoris de l'utilisateur.
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'favorites' => $favoritesMap,
        ]);
    }
}
