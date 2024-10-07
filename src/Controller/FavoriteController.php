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
    // Je récupère les favoris de l'utilisateur connecté et les affiche.
    #[Route('/', name: 'index')]
    public function index(FavoriteRepository $favoriteRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Je récupère tous les favoris de l'utilisateur actuel.
        $favorites = $favoriteRepository->findBy(['user' => $user]);

        $productFavorites = [];
        $userFavorites = [];

        // Je parcours tous les favoris récupérés de l'utilisateur.
        foreach ($favorites as $favorite) {
            // Si le favori est un produit, je l'ajoute dans la liste des produits favoris.
            if ($favorite->getProductFavorite()) {
                $productFavorites[] = $favorite->getProductFavorite();
                // Sinon, si le favori est un utilisateur, je l'ajoute dans la liste des utilisateurs favoris.
            } elseif ($favorite->getUserFavorite()) {
                $userFavorites[] = $favorite->getUserFavorite();
            }
        }

        return $this->render('favorite/index.html.twig', [
            'productFavorites' => $productFavorites,
            'userFavorites' => $userFavorites]);
    }

    // Je gère l'ajout/retrait d'un produit aux favoris de l'utilisateur connecté.
    #[Route('/toggle/product/{id}', name: 'toggle_product')]
    public function toggleProductFavorite(int $id,
                                          ProductRepository $productRepository,
                                          EntityManagerInterface $entityManager): Response
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

        // Si le produit est déjà dans les favoris, je le supprime.
        if ($existingFavorite) {
            $entityManager->remove($existingFavorite);
            $entityManager->flush();
            $this->addFlash('success', 'Produit retiré des favoris.');
        } else {
            // Sinon, je l'ajoute aux favoris de l'utilisateur.
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setProductFavorite($product);

            $entityManager->persist($favorite);
            $entityManager->flush();
            $this->addFlash('success', 'Produit ajouté aux favoris.');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    // Je gère l'ajout/retrait d'un utilisateur aux favoris de l'utilisateur connecté.
    #[Route('/toggle/user/{id}', name: 'toggle_user')]
    public function toggleUserFavorite(int $id,
                                       UserRepository $userRepository,
                                       EntityManagerInterface $entityManager): Response
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

        // Si l'utilisateur est déjà dans les favoris, je le supprime.
        if ($existingFavorite) {
            $entityManager->remove($existingFavorite);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur retiré des favoris.');
        } else {
            // Sinon, je l'ajoute aux favoris de l'utilisateur.
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
