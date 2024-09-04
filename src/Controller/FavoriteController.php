<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Enum\FavoriteType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/favorite', name: 'app_favorite_')]
class FavoriteController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur actuel
        $user = $this->getUser();

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos favoris.');
            return $this->redirectToRoute('login');
        }

        // Trouver tous les favoris pour cet utilisateur
        $favorites = $entityManager->getRepository(Favorite::class)->findBy(['user' => $user]);

        // Vérifiez les données
        dump($favorites);

        // Rendre la vue et passer les favoris à la vue Twig
        return $this->render('favorite/index.html.twig', [
            'favorites' => $favorites,
        ]);
    }


    #[Route('/add/product/{id}', name: 'add_product')]
    public function addProductToFavorite(int $id, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un favori.');
            return $this->redirectToRoute('login'); // Rediriger vers la page de connexion si non connecté
        }

        // Récupérer le produit à ajouter en favori
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        // Vérifier si le produit est déjà dans les favoris de l'utilisateur
        $existingFavorite = $entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            'productFavorite' => $product,
        ]);

        if ($existingFavorite) {
            $this->addFlash('info', 'Ce produit est déjà dans vos favoris.');
        } else {
            // Ajouter un nouveau favori
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setProductFavorite($product);
            $favorite->setFavoriteType(FavoriteType::PRODUCT);

            $entityManager->persist($favorite);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté aux favoris.');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/add/user/{id}', name: 'add_user')]
    public function addUserToFavorite(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un favori.');
            return $this->redirectToRoute('login');
        }

        // Récupérer l'utilisateur à ajouter en favori
        $favoriteUser = $userRepository->find($id);

        if (!$favoriteUser) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Vérifier si l'utilisateur est déjà dans les favoris
        $existingFavorite = $entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            'userFavorite' => $favoriteUser,
        ]);

        if ($existingFavorite) {
            $this->addFlash('info', 'Cet utilisateur est déjà dans vos favoris.');
        } else {
            // Ajouter un nouveau favori
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setUserFavorite($favoriteUser);
            $favorite->setFavoriteType(FavoriteType::USER);

            $entityManager->persist($favorite);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur ajouté aux favoris.');
        }

        return $this->redirectToRoute('app_user_show', ['id' => $favoriteUser->getId()]);
    }
}
