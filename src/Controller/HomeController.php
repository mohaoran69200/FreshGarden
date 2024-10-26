<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        ProductRepository $productRepository,
        FavoriteRepository $favoriteRepository,
        CategorieRepository $categorieRepository,
        SessionInterface $session
    ): Response {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN') && $session->get('login_origin')) {
            $session->remove('login_origin');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        // Récupérer les objets Categorie
        $categorieFruits = $categorieRepository->findOneBy(['name' => 'Fruits']);
        $categorieLegumes = $categorieRepository->findOneBy(['name' => 'Legumes']);
        $categorieAutres = $categorieRepository->findOneBy(['name' => 'Autre']);

        // Récupérer les produits par catégorie
        $fruits = $productRepository->findBy(['categorie' => $categorieFruits]);
        $legumes = $productRepository->findBy(['categorie' => $categorieLegumes]);
        $autres = $productRepository->findBy(['categorie' => $categorieAutres]);

        // Mélanger et limiter à 4 produits par catégorie
        shuffle($fruits); // Mélange le tableau des fruits
        $randomFruits = array_slice($fruits, 0, 4); // Récupère les 4 premiers

        shuffle($legumes); // Mélange le tableau des légumes
        $randomLegumes = array_slice($legumes, 0, 4); // Récupère les 4 premiers

        shuffle($autres); // Mélange le tableau des autres produits
        $randomAutres = array_slice($autres, 0, 4); // Récupère les 4 premiers

        // Gestion des favoris
        $favorites = [];
        if ($user) {
            $favorites = $favoriteRepository->findBy(['user' => $user]);
        }

        $favoritesMap = [];
        foreach ($favorites as $favorite) {
            $productFavorite = $favorite->getProductFavorite();
            if ($productFavorite !== null) {
                $favoritesMap[$productFavorite->getId()] = true;
            }
        }

        return $this->render('home/index.html.twig', [
            'fruits' => $randomFruits,
            'legumes' => $randomLegumes,
            'autres' => $randomAutres,
            'favorites' => $favoritesMap,
        ]);
    }
}
