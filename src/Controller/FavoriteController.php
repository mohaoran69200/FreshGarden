<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/favorite', name: 'app_favorite_')]
class FavoriteController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(FavoriteRepository $favoriteRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('login');
        }

        // Récupérer uniquement les favoris de l'utilisateur
        $favorites = $favoriteRepository->findBy(['user' => $user]);

        $productFavorites = [];
        $userFavorites = [];

        // Récupérer les produits et utilisateurs ajoutés en favoris
        foreach ($favorites as $favorite) {
            if ($favorite->getProductFavorite()) {
                $productFavorites[] = $favorite->getProductFavorite();
            } elseif ($favorite->getUserFavorite()) {
                $userFavorites[] = $favorite->getUserFavorite();
            }
        }

        // Passer les favoris au template
        return $this->render('favorite/index.html.twig', [
            'productFavorites' => $productFavorites,
            'userFavorites' => $userFavorites
        ]);
    }


    #[Route('/toggle/product/{id}', name: 'toggle_product')]
    public function toggleProductFavorite(int $id, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour gérer vos favoris.');
            return $this->redirectToRoute('login');
        }

        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $favoriteRepository = $entityManager->getRepository(Favorite::class);
        $existingFavorite = $favoriteRepository->findOneBy([
            'user' => $user,
            'productFavorite' => $product,
        ]);

        if ($existingFavorite) {
            // Si le produit est déjà dans les favoris, le supprimer
            $entityManager->remove($existingFavorite);
            $entityManager->flush();
            $this->addFlash('success', 'Produit retiré des favoris.');
        } else {
            // Sinon, ajouter le produit aux favoris
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setProductFavorite($product);

            $entityManager->persist($favorite);
            $entityManager->flush();
            $this->addFlash('success', 'Produit ajouté aux favoris.');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/toggle/user/{id}', name: 'toggle_user')]
    public function toggleUserFavorite(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour gérer vos favoris.');
            return $this->redirectToRoute('login');
        }

        $favoriteUser = $userRepository->find($id);

        if (!$favoriteUser) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $favoriteRepository = $entityManager->getRepository(Favorite::class);
        $existingFavorite = $favoriteRepository->findOneBy([
            'user' => $user,
            'userFavorite' => $favoriteUser,
        ]);

        if ($existingFavorite) {
            // Si l'utilisateur est déjà dans les favoris, le supprimer
            $entityManager->remove($existingFavorite);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur retiré des favoris.');
        } else {
            // Sinon, ajouter l'utilisateur aux favoris
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setUserFavorite($favoriteUser);

            $entityManager->persist($favorite);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur ajouté aux favoris.');
        }

        return $this->redirectToRoute('app_user_show', ['id' => $favoriteUser->getId()]);
    }
}
