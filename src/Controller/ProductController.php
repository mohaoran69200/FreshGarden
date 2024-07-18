<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;

#[Route('/product', name: 'app_product_')]
class ProductController extends AbstractController
{ 
    #[Route('/new', name: 'new')]
    public function new(EntityManagerInterface $entityManager): Response
        {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $name = $_POST['name'];
                $price = $_POST['price'];
                $unit = $_POST['unit'];
                $stock = $_POST['stock'];

                $product = new Product();
                $product->setName($name);
                $product->setPrice($price);
                $product->setUnit($unit);
                $product->setStock($stock);
                $product->setCreatedAt(new \DateTimeImmutable());
                $product->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->persist($product);
                $entityManager->flush();

                return $this->redirectToRoute('home');

            }
        return $this->render('product/new.html.twig', [
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
    public function remove(Product $product, EntityManagerInterface $entityManager): Response 
    {
        $entityManager->remove($product);
        $entityManager->flush();
        
        return $this->redirectToRoute('home');
    }
}
