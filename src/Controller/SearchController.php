<?php

namespace App\Controller;

use App\DTO\SearchDto;
use App\Form\SearchType;
use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository; // Ajoutez cette ligne
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
        FavoriteRepository $favoriteRepository // Injectez FavoriteRepository
    ): Response {
        // Je crée un nouvel objet SearchDto pour stocker les critères de recherche
        $search = new SearchDto();
        // Je génère le formulaire de recherche à partir du SearchDto
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        // J'initialise les résultats avec tous les produits au départ
        $results = $productRepository->findAll();

        // Si le formulaire est soumis et valide, je recherche selon les critères entrés
        if ($form->isSubmitted() && $form->isValid()) {
            $results = $productRepository->search($search);
        } else {
            // Sinon, je récupère le terme de recherche depuis les paramètres de requête
            $searchTerm = $request->query->get('search');
            if ($searchTerm) {
                // Je mets à jour l'objet SearchDto avec ce terme
                $search->setSearch($searchTerm);
                // Je recherche avec ce terme via le repository
                $results = $productRepository->search($search);
            }
        }

        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        $favorites = [];

        if ($user) {
            // Récupérer les produits favoris pour l'utilisateur
            foreach ($results as $product) {
                $favorite = $favoriteRepository->findOneBy([
                    'user' => $user,
                    'productFavorite' => $product,
                ]);
                $favorites[$product->getId()] = $favorite !== null; // Enregistre si le produit est favori
            }
        }

        // Je retourne la vue avec le formulaire, les résultats et l'état des favoris
        return $this->render('search/index.html.twig', [
            'form' => $form,
            'results' => $results,
            'favorites' => $favorites, // Passez les informations des favoris à la vue
        ]);
    }
}
