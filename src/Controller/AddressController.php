<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/account')]
class AddressController extends AbstractController
{
    #[Route('/', name: 'account', methods: ['GET'])]
    public function index(AddressRepository $addressRepository): Response
    {
        $addresses = $addressRepository->findAll();
        return $this->render('address/index.html.twig', [
            'addresses' => $addresses
        ]);
    }

    #[Route('/new', name: 'address_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();

        $fullName = $user->getFirstname() . ' ' . $user->getLastname();
        $address = new Address();
        $address
            ->setFullName($fullName)
            ->setUser($user);
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $addressRepository->save($address, true);

            if (isset($cart['products'])) return $this->redirectToRoute('checkout');

            return $this->redirectToRoute('account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('address/new.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'address_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Address $address, RequestStack $requestStack, AddressRepository $addressRepository): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $addressRepository->save($address, true);

            $session = $requestStack->getSession();

            if (!empty($session->get('checkoutData'))) {
                $data = $session->get('checkoutData');
                $data['address'] = $address;

                $session->set('checkoutData', $data);
                return $this->redirectToRoute('checkout_confirm');
            }

            return $this->redirectToRoute('account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('address/edit.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'address_delete', methods: ['POST'])]
    public function delete(Request $request, Address $address, AddressRepository $addressRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $address->getId(), $request->request->get('_token'))) {
            $addressRepository->remove($address, true);
        }

        return $this->redirectToRoute('account', [], Response::HTTP_SEE_OTHER);
    }
}
