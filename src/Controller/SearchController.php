<?php

namespace App\Controller;

use App\DTO\SearchDto;
use App\Form\SearchType;
use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/search', name: 'app_')]
class SearchController extends AbstractController
{
    // Gestion de l'affichage de la page de recherche
    #[Route('', name: 'search')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        FavoriteRepository $favoriteRepository
    ): Response {
        // Créer un objet SearchDto pour stocker les critères de recherche
        $search = new SearchDto();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        // Récupérer la page actuelle de la requête
        // Récupérer les résultats paginés en fonction du formulaire
        if (!$form->isSubmitted() || !$form->isValid()) {
            $searchTerm = $request->query->get('search');
            if ($searchTerm) {
                $search->setSearch($searchTerm);
            }
        }
        $results = $productRepository->search($search, $request->query->getInt('page', 1));

        // Vérifier si l'utilisateur est connecté pour obtenir ses favoris
        $user = $this->getUser();
        $favorites = [];

        if ($user) {
            // Récupérer les produits favoris pour l'utilisateur
            foreach ($results as $product) {
                $favorite = $favoriteRepository->findOneBy([
                    'user' => $user,
                    'productFavorite' => $product,
                ]);
                $favorites[$product->getId()] = $favorite !== null;
            }
        }

        // Retourner la vue avec le formulaire, les résultats paginés et l'état des favoris
        return $this->render('search/index.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
            'favorites' => $favorites,
        ]);
    }
}
