<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CheckoutType;
use App\Services\CartService;
use App\Services\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function checkoutConfirm(Request $request, RequestStack $requestStack, OrderService $orderService): Response
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
        $session = $requestStack->getSession();


        if (($form->isSubmitted() && $form->isValid()) || !empty($session->get('checkoutData'))) {
            if (!empty($session->get('checkoutData'))) {
                $data = $session->get('checkoutData');
            } else {
                $data = $form->getData();
                $session->set('checkoutData', $data);
            }

            $cart['checkout'] = $data;
            $id = $orderService->saveCart($cart, $user);
        }
        
        return $this->render('checkout/checkout.html.twig', [
            'cart' => $cart,
            'data' => $data,
            'form' => $form->createView(),
            'id' => $id
        ]);
    }
}
