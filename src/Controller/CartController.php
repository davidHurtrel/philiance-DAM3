<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/cart', name: 'cart')]
    public function index(ProductRepository $productRepository): Response
    {
        $sessionCart = $this->requestStack->getSession()->get('cart', []);
        $cart = [];
        $total = 0;
        foreach ($sessionCart as $id => $quantity) {
            $product = $productRepository->find($id);
            $element = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $cart[] = $element;
            $total += $product->getPrice() * $quantity;
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(int $id): Response
    {
        $cart = $this->requestStack->getSession()->get('cart', []); // récupère le panier existant ou un tableau vide
        if (!empty($cart[$id])) { // si le produit est déjà dans le panier
            $cart[$id]++; // incrémente de 1 sa quantité
        } else {
            $cart[$id] = 1; // définit la quantité de ce produit à 1
        }
        $this->requestStack->getSession()->set('cart', $cart); // redéfinit le panier en session avec sa nouvelle valeur
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(int $id): Response
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        if (!empty($cart[$id])) { // vérifie que le produit est bien présent dans le panier
            if ($cart[$id] > 1) { // si la quantité dans le panier est strictement supérieure à 1
                $cart[$id]--; // décrémente la quantité
            } else {
                unset($cart[$id]); // supprime le produit du panier
            }
        }
        $this->requestStack->getSession()->set('cart', $cart);
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/delete/{id}', name: 'cart_delete')]
    public function delete(int $id): Response
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }
        $this->requestStack->getSession()->set('cart', $cart);
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(): Response
    {
        $this->requestStack->getSession()->remove('cart');
        return $this->redirectToRoute('cart');
    }
}
