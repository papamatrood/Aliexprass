<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Cart;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Stripe\Checkout\Session;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{

    #[Route('/create-checkout-session/{id<\d+>}', name: 'create_checkout_session')]
    public function index(Cart $cart, OrderService $orderService): Response
    {        
        Stripe::setApiKey(
            'sk_test_51LnO4bJ6aGFkZ0p6ed3XqCzK7pwr6OAo0LH7N0Ak3phulblsZbTRyEWkILGhVAr66WSC7MV9ijj335hfcwMfXAPN00OGfz7BXu'
        );

        $stripeService = new StripeService();

        $id = $orderService->createOrder($cart);

        $session = Session::create([
            'line_items' => $stripeService->getLineItems($cart),
            'mode' => 'payment',
            'success_url' => $_ENV['MY_DOMAINE'] . '/success?id=' . $id,
            'cancel_url' => $_ENV['MY_DOMAINE'] . '/cancel',
          ]);

          return $this->redirect($session->url, 303);
          
    }



    #[Route('/success', name: 'stripe_success', methods: ['GET'])]
    public function success(OrderRepository $orderRepo, CartService $cartService, EntityManagerInterface $manager, Request $request): Response
    {
        $cartService->removeCart();

        $id = (int) $request->get('id');

        $order = $orderRepo->find($id);

        if (!$order) {
            $this->redirectToRoute('home');
        }

        $order->setIsPaid(true);
        $manager->flush();

        return $this->render('stripe/success.html.twig');
    }

    #[Route('/cancel', name: 'stripe_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }
}
