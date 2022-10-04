<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'product')]
    public function index(ProductRepository $productRepo): Response
    {
        $isBestSeller = $productRepo->findByIsBestSeller(true);
        $isNewArrival = $productRepo->findByIsNewArrival(true);
        $isFeatured = $productRepo->findByIsFeatured(true);
        $isSpecialOffer = $productRepo->findByIsSpecialOffer(true);
        
        return $this->render('product/index.html.twig', [
            'isBestSeller'   => $isBestSeller,
            'isNewArrival'   => $isNewArrival,
            'isFeatured'     => $isFeatured,
            'isSpecialOffer' => $isSpecialOffer,
        ]);
    }
}
