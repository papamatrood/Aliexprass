<?php

namespace App\Controller;

use App\Services\CartService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart')]
class CartController extends AbstractController
{

    public function __construct(private CartService $cartService)
    {
    }

    #[Route('/', name: 'cart')]
    public function index(): Response
    {
        $cart = $this->cartService->getFullCart();
        if(!isset($cart['products'])) return $this->redirectToRoute('product');
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id<\d+>}', name: 'add_cart')]
    public function addToCart(int $id): Response
    {
        $this->cartService->addToCart($id);

        return $this->redirectToRoute('cart');
    }

    #[Route('/delete/{id<\d+>}', name: 'delete_cart')]
    public function deleteToCart(int $id): Response
    {
        $this->cartService->deleteToCart($id);

        return $this->redirectToRoute('cart');
    }

    #[Route('/delete-all/{id<\d+>}', name: 'delete_all_cart')]
    public function deleteAllToCart(int $id): Response
    {
        $this->cartService->deleteAllToCart($id);

        return $this->redirectToRoute('cart');
    }

    #[Route('/remove', name: 'remove_cart')]
    public function removeCart(int $id): Response
    {
        $this->cartService->removeCart();

        return $this->redirectToRoute('cart');
    }

}
