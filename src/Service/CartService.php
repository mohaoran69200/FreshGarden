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
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }
// App/Service/CartService.php

    public function addToCart(int $id, int $quantity = 1): void
    {
        $cart = $this->getSession()->get('cart', []);
        $currentQuantity = $cart[$id] ?? 0;
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if ($product) {
            $maxQuantity = min($quantity, $product->getStock()); // Assure de ne pas dépasser le stock
            $cart[$id] = min($currentQuantity + $maxQuantity, $product->getStock());
            $this->getSession()->set('cart', $cart);
        }
    }

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


    public function removeToCart(int $id): void
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        unset($cart[$id]);
        $this->getSession()->set('cart', $cart);
    }

    public function removeCartAll()
    {
        return $this->getSession()->remove('cart');
    }

    public function getTotal(): array
    {
        $cart = $this->getSession()->get('cart');
        $cartData = [];
        if($cart) {
            foreach ($cart as $id => $quantity)
            {
                $product = $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
                if (!$product)
                {
                    continue;
                }
                $cartData[] =
                    [
                        'product' => $product,
                        'quantity' => $quantity
                    ];
            }
        }
        return $cartData;
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}