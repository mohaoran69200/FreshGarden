<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProductType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product', name: 'app_product_')]
class ProductController extends AbstractController
{
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
            $product
                ->setUser($this->getUser())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit-product/{id}', name: 'update')]
    #[IsGranted('ROLE_USER')]
    public function update(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifiez que l'utilisateur connecté est le propriétaire du produit
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }
        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    #[Route('/show/{id}', name: 'show')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(Product $product, EntityManagerInterface $entityManager): Response
    {
        // Vérifiez que l'utilisateur connecté est le propriétaire du produit
        if ($this->getUser() !== $product->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce produit.');
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
