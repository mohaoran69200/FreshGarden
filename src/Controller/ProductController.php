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

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    #[Route('/new', name: 'new')]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUser($this->getUser())  // L'utilisateur connecté est défini comme propriétaire du produit
            ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

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

    #[Route('/update/{id}', name: 'update')]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('edit', $product);  // Vérifiez si l'utilisateur peut éditer le produit

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Produit mis à jour avec succès.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/show/{id}', name: 'show')]
    public function show(Product $product,
                         FavoriteRepository $favoriteRepository): Response
    {
        $user = $this->getUser();
        $isFavorite = false;

        if ($user) {
            $favorite = $favoriteRepository->findOneBy([
                'user' => $user,
                'productFavorite' => $product,
            ]);

            $isFavorite = $favorite !== null;
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'isFavorite' => $isFavorite,  // Assurez-vous que cette ligne est incluse
        ]);
    }


    #[Route('/remove/{id}', name: 'remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(Product $product,
                           EntityManagerInterface $entityManager): Response {
        $this->denyAccessUnlessGranted('delete', $product);  // Vérifiez si l'utilisateur peut supprimer le produit

        $entityManager->remove($product);
        $entityManager->flush();
        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('home');
    }

    #[Route('/fruits', name: 'fruits')]
    public function fruits(ProductRepository $productRepository,
                           CategorieRepository $categorieRepository,
                            FavoriteRepository $favoriteRepository): Response
    {

        $categorie = $categorieRepository->findOneBy(['name' => 'Fruits']);
        $products = $productRepository->findBy(['categorie' => $categorie]);

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

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Fruits',
            'isFavorite' => $isFavorite,
        ]);
    }

    #[Route('/legumes', name: 'legumes')]
    public function legumes(ProductRepository $productRepository,
                           CategorieRepository $categorieRepository,
                           FavoriteRepository $favoriteRepository): Response
    {

        $categorie = $categorieRepository->findOneBy(['name' => 'Légumes']);
        $products = $productRepository->findBy(['categorie' => $categorie]);

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

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Légumes',
            'isFavorite' => $isFavorite,
        ]);
    }

    #[Route('/autres', name: 'autres')]
    public function autres(ProductRepository $productRepository,
                           CategorieRepository $categorieRepository,
                           FavoriteRepository $favoriteRepository): Response
    {

        $categorie = $categorieRepository->findOneBy(['name' => 'Autre']);
        $products = $productRepository->findBy(['categorie' => $categorie]);

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

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorie' => 'Autre',
            'isFavorite' => $isFavorite,
        ]);
    }
}
