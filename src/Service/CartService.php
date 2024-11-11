<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;

    // Constructeur pour initialiser les services nécessaires
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    // Ajoute un produit au panier avec une quantité spécifiée (ou 1 par défaut)
    public function addToCart(int $id, int $quantity = 1): void
    {
        $cart = $this->getSession()->get('cart', []);
        $currentQuantity = $cart[$id] ?? 0;
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if ($product) {
            $maxQuantity = min($quantity, $product->getStock());
            $cart[$id] = min($currentQuantity + $maxQuantity, $product->getStock());
            $this->getSession()->set('cart', $cart);
        }
    }

    // Met à jour la quantité d'un produit dans le panier
    public function updateCartQuantity(int $id, int $quantity): void
    {
        $cart = $this->getSession()->get('cart', []);
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if ($product && $quantity > 0) {
            $cart[$id] = min($quantity, $product->getStock());
        } elseif ($quantity === 0) {
            unset($cart[$id]);
        }

        $this->getSession()->set('cart', $cart);
    }

    // Supprime un produit du panier
    public function removeToCart(int $id): void
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        unset($cart[$id]);
        $this->getSession()->set('cart', $cart);
    }

    // Supprime tous les produits du panier
    public function removeCartAll(): void
    {
        $this->getSession()->remove('cart');
    }

    /**
     * Calcule et retourne le total des produits dans le panier.
     *
     * @return array<string, mixed>[] Un tableau contenant des tableaux avec 'product' (Product) et 'quantity' (int).
     */
    public function getTotal(): array
    {
        $cart = $this->getSession()->get('cart');
        $cartData = [];
        if ($cart) {
            foreach ($cart as $id => $quantity) {
                $product = $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
                if (!$product) {
                    continue;
                }
                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
            }
        }
        return $cartData;
    }

    // Récupération de la session de l'utilisateur
    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}

