<?php

namespace App\Controller\Admin;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/product')]
class AdminProductController extends AbstractController
{
    #[Route('/', name: 'app_admin_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        // Récupérer la page actuelle, avec 1 par défaut
        $page = $request->query->getInt('page', 1);

        // Récupérer les produits paginés pour l'admin
        $products = $productRepository->findAllPaginatedForAdmin($page);

        return $this->render('admin/admin_product/index.html.twig', [
            'products' => $products,
        ]);
    }
}
