<?php

namespace App\Controller;

use App\Form\CheckoutType;
use App\Services\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(private CartService $cartService)
    {
    }

    #[Route('/', name: 'checkout')]
    public function index(Request $request): Response
    {
        $cart = $this->cartService->getFullCart();
        if (!isset($cart['products'])) return $this->redirectToRoute('product');

        /**
         *@var User
         */
        $user = $this->getUser();

        if (empty($user->getAddresses())) {
            $this->addFlash('message_flash', 'Veuillez ajouter une adresse afin de faire le payement.');
            return $this->redirectToRoute('address_new');
        }

        $form = $this->createForm(CheckoutType::class, null, ['user' => $user]);
        $form->handleRequest($request);

        return $this->render('checkout/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView()
        ]);
    }

    #[Route('/confirm', name: 'checkout_confirm')]
    public function checkoutConfirm(Request $request): Response
    {
        $cart = $this->cartService->getFullCart();
        if (!isset($cart['products'])) return $this->redirectToRoute('product');

        /**
         *@var User
         */
        $user = $this->getUser();

        if (empty($user->getAddresses())) {
            $this->addFlash('message_flash', 'Veuillez ajouter une adresse afin de faire le payement.');
            return $this->redirectToRoute('address_new');
        }

        $form = $this->createForm(CheckoutType::class, null, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
        }

        return $this->render('checkout/confirm.html.twig', [
            'cart' => $cart,
            'data' => $data,
            'form' => $form->createView()
        ]);
    }
}
