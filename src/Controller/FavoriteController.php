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
            $this->addFlash('error', 'Vous devez être connecté pour voir vos favoris.');
            return $this->redirectToRoute('login');
        }

        $favorites = $favoriteRepository->findBy(['user' => $user]);

        $productFavorites = [];
        $userFavorites = [];

        foreach ($favorites as $favorite) {
            if ($favorite->getProductFavorite()) {
                $productFavorites[] = $favorite->getProductFavorite();
            } elseif ($favorite->getUserFavorite()) {
                $userFavorites[] = $favorite->getUserFavorite();
            }
        }

        return $this->render('favorite/index.html.twig', [
            'productFavorites' => $productFavorites,
            'userFavorites' => $userFavorites,
        ]);
    }

    #[Route('/add/product/{id}', name: 'add_product')]
    public function addProductToFavorite(int $id, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un favori.');
            return $this->redirectToRoute('login');
        }

        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $existingFavorite = $entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            'productFavorite' => $product,
        ]);

        if ($existingFavorite) {
            $this->addFlash('info', 'Ce produit est déjà dans vos favoris.');
        } else {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setProductFavorite($product);

            $entityManager->persist($favorite);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté aux favoris.');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/add/user/{id}', name: 'add_user')]
    public function addUserToFavorite(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un favori.');
            return $this->redirectToRoute('login');
        }

        $favoriteUser = $userRepository->find($id);

        if (!$favoriteUser) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $existingFavorite = $entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            'userFavorite' => $favoriteUser,
        ]);

        if ($existingFavorite) {
            $this->addFlash('info', 'Cet utilisateur est déjà dans vos favoris.');
        } else {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setUserFavorite($favoriteUser);

            $entityManager->persist($favorite);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur ajouté aux favoris.');
        }

        return $this->redirectToRoute('app_user_show', ['id' => $favoriteUser->getId()]);
    }

    #[Route('/remove/product/{id}', name: 'remove_product')]
    public function removeProductFromFavorite(int $id, FavoriteRepository $favoriteRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un favori.');
            return $this->redirectToRoute('login');
        }

        $favorite = $favoriteRepository->findOneBy([
            'user' => $user,
            'productFavorite' => $id,
        ]);

        if (!$favorite) {
            $this->addFlash('error', 'Produit non trouvé dans vos favoris.');
        } else {
            $entityManager->remove($favorite);
            $entityManager->flush();
            $this->addFlash('success', 'Produit retiré des favoris.');
        }

        return $this->redirectToRoute('app_favorite_index');
    }

    #[Route('/remove/user/{id}', name: 'remove_user')]
    public function removeUserFromFavorite(int $id, FavoriteRepository $favoriteRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un favori.');
            return $this->redirectToRoute('login');
        }

        $favorite = $favoriteRepository->findOneBy([
            'user' => $user,
            'userFavorite' => $id,
        ]);

        if (!$favorite) {
            $this->addFlash('error', 'Utilisateur non trouvé dans vos favoris.');
        } else {
            $entityManager->remove($favorite);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur retiré des favoris.');
        }

        return $this->redirectToRoute('app_favorite_index');
    }
}
