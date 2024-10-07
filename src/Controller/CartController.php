<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    // Je récupère et j'affiche le contenu total du panier en utilisant le service CartService.
    #[Route('/', name: 'index')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig',
            ['cart' => $cartService->getTotal()]);
    }

    // J'ajoute un produit au panier en fonction de l'ID et je redirige vers la page du panier.
    #[Route('/add/{id}', name: 'add')]
    public function addToCart(CartService $cartService, int $id): Response
    {
        $cartService->addToCart($id);
        return $this->redirectToRoute('cart_index');
    }

    // Je retire un produit du panier en fonction de l'ID et je redirige vers la page du panier.
    #[Route('/remove/{id}', name: 'remove')]
    public function removeToCart(CartService $cartService, int $id): Response
    {
        $cartService->removeToCart($id);
        return $this->redirectToRoute('cart_index');
    }

    // Je vide complètement le panier et redirige l'utilisateur vers la page d'accueil.
    #[Route('/removeAll', name: 'removeAll')]
    public function removeAll(CartService $cartService): Response
    {
        $cartService->removeCartAll();
        return $this->redirectToRoute('home');
    }
}
