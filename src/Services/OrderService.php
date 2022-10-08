<?php

namespace App\Services;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\CartDetails;
use App\Entity\OrderDetails;
use App\Services\CartService;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{

    public function __construct(
        private EntityManagerInterface $manager,
        private CartService $cartService,
        private ProductRepository $productRepo
    ) {
    }

    public function saveCart(array $data, User $user): int
    {
        $reference         = $this->getReference();
        $products          = $data['products'];
        $address           = $data['checkout']['address'];
        $carrier           = $data['checkout']['carrier'];
        $moreInformation   = $data['checkout']['moreInformation'] ?? null;

        $cart = new Cart();
        $cart
            ->setReference($reference)
            ->setFullName($address->getFullName())
            ->setDeliveryAddress($address->getAddress())
            ->setCarrierName($carrier->getName())
            ->setCarrierPrice($carrier->getPrice())
            ->setMoreInformation($moreInformation)
            ->setQuantity($data['data']['QUANTITIES'])
            ->setSubTotalHT($data['data']['THT'])
            ->setTaxe($data['data']['TVA'])
            ->setSubTotalTTC($data['data']['TTC'])
            ->setUser($user);
        $this->manager->persist($cart);

        foreach ($products as $key => $values) {
            $product = $values['product'];

            $cartDetails = new CartDetails();
            $cartDetails
                ->setProductName($product->getName())
                ->setProductPrice($product->getPrice())
                ->setQuantity($values['quantity'])
                ->setSubTotalHT($values['productHT'])
                ->setTaxe($values['productTVA'])
                ->setSubTotalTTC($values['productTTC'])
                ->setCart($cart);
            $this->manager->persist($cartDetails);
        }

        $this->manager->flush();

        return $cart->getId();
    }

    public function createOrder(Cart $cart): int
    {
        $order = new Order();
        $order
            ->setReference($cart->getReference())
            ->setFullName($cart->getFullName())
            ->setDeliveryAddress($cart->getDeliveryAddress())
            ->setCarrierName($cart->getCarrierName())
            ->setCarrierPrice($cart->getCarrierPrice())
            ->setMoreInformation($cart->getMoreInformation())
            ->setQuantity($cart->getQuantity())
            ->setSubTotalHT($cart->getSubTotalHT())
            ->setTaxe($cart->getTaxe())
            ->setSubTotalTTC($cart->getSubTotalTTC())
            ->setUser($cart->getUser());
        $this->manager->persist($order);

        foreach ($cart->getCartDetails()->getValues() as $cartDetail) {

            $orderDetails = new OrderDetails();
            $orderDetails
                ->setProductName($cartDetail->getProductName())
                ->setProductPrice($cartDetail->getProductPrice())
                ->setQuantity($cartDetail->getQuantity())
                ->setSubTotalHT($cartDetail->getSubTotalHT())
                ->setTaxe($cartDetail->getTaxe())
                ->setSubTotalTTC($cartDetail->getSubTotalTTC())
                ->setOrders($order);
            $this->manager->persist($orderDetails);
        }

        $this->manager->flush();

        return $order->getId();
    }

    private function getReference(): string
    {
        return 'ML/BKO/' . date('d-m-Y-H:i:s') . '/' . uniqid() . '/' . rand();
    }
}
