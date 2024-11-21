<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    // Affiche le contenu total du panier
    #[Route('/', name: 'index')]
    public function index(CartService $cartService): Response
    {
        $cartItems = $cartService->getTotal();
        return $this->render('cart/index.html.twig', ['cart' => $cartItems]);
    }

    // Ajoute un produit au panier
    #[Route('/add/{id}', name: 'add')]
    public function addToCart(CartService $cartService, int $id): Response
    {
        $cartService->addToCart($id);
        return $this->redirectToRoute('cart_index');
    }

    // Met à jour la quantité d'un produit dans le panier
    #[Route('/update/{id}', name: 'update_quantity')]
    public function updateQuantity(CartService $cartService, int $id, Request $request): Response
    {
        $quantity = (int)$request->request->get('quantity', 0);
        $cartService->updateCartQuantity($id, $quantity);
        return $this->redirectToRoute('cart_index');
    }

    // Retire un produit du panier
    #[Route('/remove/{id}', name: 'remove')]
    public function removeToCart(CartService $cartService, int $id): Response
    {
        $cartService->removeToCart($id);
        return $this->redirectToRoute('cart_index');
    }

    // Vide le panier
    #[Route('/removeAll', name: 'removeAll')]
    public function removeAll(CartService $cartService): Response
    {
        $cartService->removeCartAll();
        return $this->redirectToRoute('home');
    }
}
