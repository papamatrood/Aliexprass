<?php

namespace App\Services;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $session = null;

    private $productRepo = null;

    const TAXE = 0.18;


    public function __construct(private RequestStack $requestStack, ProductRepository $productRepo)
    {
        $this->session = $this->requestStack->getSession();
        $this->productRepo = $productRepo;
    }

    public function addToCart(int $id): void
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->updateCart($cart);
    }

    public function deleteToCart(int $id): void
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $this->updateCart($cart);
    }

    public function deleteAllToCart(int $id): void
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $this->updateCart($cart);
    }

    public function removeCart(): void
    {
        $this->session->set('cart', []);
        $this->session->set('cartData', []);
    }

    public function updateCart(array $cart): void
    {
        $this->session->set('cart', $cart);
        $this->session->set('cartData', $this->getFullCart());
    }

    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    public function getFullCart(): array
    {
        $cart = $this->getCart();
        $data = [];
        $THT = 0;
        $quantities = 0;

        foreach ($cart as $id => $quantity) {

            $product    = $this->productRepo->find($id);
            $productHT  = round($quantity * $product->getPrice(), 2);
            $productTVA = $productHT * self::TAXE;
            $productTTC = round($productHT * (1 + self::TAXE), 2);
            $quantities = $quantities + $quantity;

            $THT        = $THT + $productHT;

            $data['products'][] = [
                'product' => $this->productRepo->find($id),
                'quantity' => $quantity,
                'productHT' => $productHT,
                'productTVA' => $productTVA,
                'productTTC' => $productTTC,
            ];

        }

        $data['data'] = [
            'THT' => round($THT, 2),
            'TVA' => $THT * (self::TAXE),
            'TTC' => round($THT * (1 + self::TAXE), 2),
            'TAXE' => (self::TAXE * 100) . '%',
            'QUANTITIES' => $quantities,
        ];

        return $data;
    }
    
}
