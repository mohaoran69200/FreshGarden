<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\FavoriteRepository;
use App\Repository\CategorieRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProductType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product', name: 'app_product_')]
class ProductController extends AbstractController
{
    // Je crée un nouveau produit
    #[Route('/new', name: 'new')]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User|null $user */
            $user = $this->getUser();

            if (!$user instanceof User) {
                throw new LogicException('L\'utilisateur doit être connecté pour créer un produit.');
            }

            $product->setUser($user)
                ->setCreatedAt(new DateTimeImmutable())
                ->setUpdatedAt(new DateTimeImmutable());


            // Je persiste le produit dans la base de données
            $entityManager->persist($product);
            $entityManager->flush();


            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs.');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    // Je modifie un produit existant
    #[Route('/update/{id}', name: 'update')]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('edit', $product);  // Je vérifie que l'utilisateur peut modifier ce produit

        $form = $this->createForm(ProductType::class, $product);  // Je crée le formulaire pour l'édition du produit
        $form->handleRequest($request);  // Je traite la requête du formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Produit mis à jour avec succès.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }


    // La page d'un produit
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
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
    public function remove(Product $product, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $product);

        $entityManager->remove($product);  // Je supprime le produit de la base de données
        $entityManager->flush();  // Je sauvegarde cette action en base de données
        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('home');  // Je redirige vers la page d'accueil
    }


    // Je récupère et affiche les produits de la catégorie "Fruits"
    #[Route('/fruits', name: 'fruits')]
    public function fruits(
        ProductRepository $productRepository,
        CategorieRepository $categorieRepository,
        FavoriteRepository $favoriteRepository,
        Request $request
    ): Response {
        $categorie = $categorieRepository->findOneBy(['name' => 'Fruits']);

        // Utiliser la méthode dans le repository pour récupérer les produits paginés

        $products = $productRepository->findByCategoryPaginated($categorie, $request->query->getInt('page', 1));

        // Récupérer l'utilisateur et vérifier les favoris
        $user = $this->getUser();
        $isFavorite = false;

        if ($user) {
            foreach ($products as $product) {
                $favorite = $favoriteRepository->findOneBy([
                    'user' => $user,
                    'productFavorite' => $product,
                ]);
                $isFavorite = $favorite !== null;
            }
        }
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

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Fruits',
            'isFavorite' => $isFavorite,
            'favorites' => $favoritesMap,
        ]);
    }


    // Je récupère et affiche les produits de la catégorie "Légumes"
    #[Route('/legumes', name: 'legumes')]
    public function legumes(
        ProductRepository $productRepository,
        CategorieRepository $categorieRepository,
        FavoriteRepository $favoriteRepository,
        Request $request
    ): Response {

        $categorie = $categorieRepository->findOneBy(['name' => 'Légumes']);

        // Utiliser la méthode dans le repository pour récupérer les produits paginés


        $products = $productRepository->findByCategoryPaginated($categorie, $request->query->getInt('page', 1));



        $user = $this->getUser();
        $isFavorite = false;
        if ($user) {
            foreach ($products as $product) {
                $favorite = $favoriteRepository->findOneBy([
                    'user' => $user,
                    'productFavorite' => $product,
                ]);
                $isFavorite = $favorite !== null;
            }
        }
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

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Légumes',
            'isFavorite' => $isFavorite,
            'favorites' => $favoritesMap,
        ]);
    }


    // Je récupère et affiche les produits de la catégorie "Produits divers"
    #[Route('/autres', name: 'autres')]
    public function autres(
        ProductRepository $productRepository,
        CategorieRepository $categorieRepository,
        FavoriteRepository $favoriteRepository,
        Request $request
    ): Response {
        $categorie = $categorieRepository->findOneBy(['name' => 'Autre']);

        // Utiliser la méthode dans le repository pour récupérer les produits paginés
        $page = $request->query->getInt('page', 1);
        $products = $productRepository->findByCategoryPaginated($categorie, $page);

        $user = $this->getUser();
        $isFavorite = false;
        if ($user) {
            foreach ($products as $product) {
                $favorite = $favoriteRepository->findOneBy([
                    'user' => $user,
                    'productFavorite' => $product,
                ]);
                $isFavorite = $favorite !== null;
            }
        }
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

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Produits divers',
            'isFavorite' => $isFavorite,
            'favorites' => $favoritesMap,
        ]);
    }
}
