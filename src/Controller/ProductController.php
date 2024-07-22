<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Entity\Image;
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

    #[Route('/update/{id}', name: 'update')]
    #[IsGranted('ROLE_USER')]
    public function update(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('product/new.html.twig', [
            'form' => $form,
            'product' => $product
        ]);
    }

    #[Route('/show/{id}', name: 'show')]
    public function show(Product $product): Response
    {
        $product->getcategorie();
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(Product $product, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
