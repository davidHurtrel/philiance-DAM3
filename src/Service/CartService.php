<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    protected $requestStack;
    protected $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    public function add(int $id): void
    {
        $cart = $this->requestStack->getSession()->get('cart', []); // récupère le panier existant ou un tableau vide
        if (!empty($cart[$id])) { // si le produit est déjà dans le panier
            $cart[$id]++; // incrémente de 1 sa quantité
        } else {
            $cart[$id] = 1; // définit la quantité de ce produit à 1
        }
        $this->requestStack->getSession()->set('cart', $cart); // redéfinit le panier en session avec sa nouvelle valeur
    }

    public function remove(int $id): void
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
    }

    public function delete(int $id): void
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }
        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove('cart');
    }

    public function getCart(): array
    {
        $sessionCart = $this->requestStack->getSession()->get('cart', []);
        $cart = [];
        foreach ($sessionCart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            $element = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $cart[] = $element;
        }
        return $cart;
    }

    public function getTotal(): float
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $total = 0;
        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            $total += $product->getPrice() * $quantity;
        }
        return $total;
    }

    public function getNbProducts(): int
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $nb = 0;
        foreach ($cart as $id => $quantity) {
            $nb += $quantity; // nombre de produits au total
            // $nb++; // nombre de produits différents
        }
        return $nb;
    }
}
