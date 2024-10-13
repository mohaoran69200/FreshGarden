<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\FavoriteRepository;
use App\Repository\CategorieRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProductType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product', name: 'app_product_')]
class ProductController extends AbstractController
{
    private AuthorizationCheckerInterface $authChecker;

    // J'initialise l'AuthorizationChecker
    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    // Je crée un nouveau produit
    #[Route('/new', name: 'new')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        $product = new Product();  // Je crée un nouvel objet produit
        $form = $this->createForm(ProductType::class, $product);  // Je génère le formulaire pour le produit
        $form->handleRequest($request);  // Je traite la requête du formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // J'ajoute les informations de l'utilisateur et la date de création/mise à jour
            $product->setUser($this->getUser())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            // Je persiste le produit dans la base de données
            $entityManager->persist($product);
            $entityManager->flush();

            // Je notifie l'utilisateur du succès
            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        } elseif ($form->isSubmitted()) {
            // Si le formulaire est soumis mais invalide, je notifie l'utilisateur
            $this->addFlash('error', 'Le formulaire contient des erreurs.');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Je modifie un produit existant
    #[Route('/update/{id}', name: 'update')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $product);  // Je vérifie que l'utilisateur peut modifier ce produit

        $form = $this->createForm(ProductType::class, $product);  // Je crée le formulaire pour l'édition du produit
        $form->handleRequest($request);  // Je traite la requête du formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTimeImmutable());  // Je mets à jour la date de modification
            $entityManager->flush();  // Je sauvegarde les changements en base de données
            $this->addFlash('success', 'Produit mis à jour avec succès.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    // Je montre les détails d'un produit
    #[Route('/show/{id}', name: 'show')]
    public function show(Product $product, FavoriteRepository $favoriteRepository): Response
    {
        $user = $this->getUser();  // Je récupère l'utilisateur connecté
        $isFavorite = false;

        // Si l'utilisateur est connecté, je vérifie si le produit est dans ses favoris
        if ($user) {
            $favorite = $favoriteRepository->findOneBy([
                'user' => $user,
                'productFavorite' => $product,
            ]);

            $isFavorite = $favorite !== null;  // Si un favori est trouvé, je marque le produit comme favori
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'isFavorite' => $isFavorite,  // Je passe l'information du favori à la vue
        ]);
    }

    // Je supprime un produit
    #[Route('/remove/{id}', name: 'remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(Product $product, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $product);  // Je vérifie que l'utilisateur a le droit de supprimer ce produit

        $entityManager->remove($product);  // Je supprime le produit de la base de données
        $entityManager->flush();  // Je sauvegarde cette action en base de données
        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('home');  // Je redirige vers la page d'accueil
    }

    // Je récupère et affiche les produits de la catégorie "Fruits"
    #[Route('/fruits', name: 'fruits')]
    public function fruits(ProductRepository $productRepository,
                           CategorieRepository $categorieRepository,
                           FavoriteRepository $favoriteRepository): Response
    {
        $categorie = $categorieRepository->findOneBy(['name' => 'Fruits']);  // Je récupère la catégorie "Fruits"
        $products = $productRepository->findBy(['categorie' => $categorie]);  // Je récupère les produits associés

        $user = $this->getUser();  // Je récupère l'utilisateur connecté
        $isFavorite = false;

        // Si l'utilisateur est connecté, je vérifie chaque produit pour voir s'il est dans ses favoris
        if ($user) {
            foreach ($products as $product) {
                $favorite = $favoriteRepository->findOneBy([
                    'user' => $user,
                    'productFavorite' => $product,
                ]);
                $isFavorite = $favorite !== null;  // Je mets à jour le statut de favori pour chaque produit
            }
        }

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Fruits',
            'isFavorite' => $isFavorite,
        ]);
    }

    // Je récupère et affiche les produits de la catégorie "Légumes"
    #[Route('/legumes', name: 'legumes')]
    public function legumes(ProductRepository $productRepository,
                            CategorieRepository $categorieRepository,
                            FavoriteRepository $favoriteRepository): Response
    {
        $categorie = $categorieRepository->findOneBy(['name' => 'Légumes']);  // Je récupère la catégorie "Légumes"
        $products = $productRepository->findBy(['categorie' => $categorie]);  // Je récupère les produits associés

        $user = $this->getUser();  // Je récupère l'utilisateur connecté
        $isFavorite = false;

        // Si l'utilisateur est connecté, je vérifie chaque produit pour voir s'il est dans ses favoris
        if ($user) {
            foreach ($products as $product) {
                $favorite = $favoriteRepository->findOneBy([
                    'user' => $user,
                    'productFavorite' => $product,
                ]);
                $isFavorite = $favorite !== null;  // Je mets à jour le statut de favori pour chaque produit
            }
        }

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Légumes',
            'isFavorite' => $isFavorite,
        ]);
    }

    // Je récupère et affiche les produits de la catégorie "Autres"
    #[Route('/autres', name: 'autres')]
    public function autres(ProductRepository $productRepository,
                           CategorieRepository $categorieRepository,
                           FavoriteRepository $favoriteRepository): Response
    {
        $categorie = $categorieRepository->findOneBy(['name' => 'Autre']);  // Je récupère la catégorie "Autres"
        $products = $productRepository->findBy(['categorie' => $categorie]);  // Je récupère les produits associés

        $user = $this->getUser();  // Je récupère l'utilisateur connecté
        $isFavorite = false;

        // Si l'utilisateur est connecté, je vérifie chaque produit pour voir s'il est dans ses favoris
        if ($user) {
            foreach ($products as $product) {
                $favorite = $favoriteRepository->findOneBy([
                    'user' => $user,
                    'productFavorite' => $product,
                ]);
                $isFavorite = $favorite !== null;  // Je mets à jour le statut de favori pour chaque produit
            }
        }

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Autre',
            'isFavorite' => $isFavorite,
        ]);
    }
}
