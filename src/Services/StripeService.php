<?php

namespace App\Services;

use App\Entity\Cart;

class StripeService
{

    public function getLineItems(Cart $cart): array
    {
        $line_items = [];

        $cartDetails = $cart->getCartDetails()->getValues();

        foreach ($cartDetails as $cartDetail) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'xof',
                    'product_data' => [
                        'name' => $cartDetail->getProductName(),
                    ],
                    'unit_amount' => $cartDetail->getProductPrice(),
                ],
                'quantity' => $cartDetail->getQuantity(),
            ];
        }

        // Carrier
        $line_items[] = [
            'price_data' => [
                'currency' => 'xof',
                'product_data' => [
                    'name' => 'Carrier Name : ' . $cart->getCarrierName(),
                ],
                'unit_amount' => $cart->getCarrierPrice(),
            ],
            'quantity' => 1,
        ];

        return $line_items;
    }
}
