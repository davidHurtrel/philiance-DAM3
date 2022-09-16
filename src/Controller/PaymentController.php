<?php

namespace App\Controller;

use App\Entity\Order;
use Stripe\StripeClient;
use App\Service\CartService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentController extends AbstractController
{
    #[Route('/payment/{order}', name: 'payment')]
    public function index(Request $request, CartService $cartService, Order $order): Response
    {
        // si on ne vient pas de la page de validation du panier, on redirige
        if ($request->headers->get('referer') !== 'https://127.0.0.1:8000/cart/validation') {
            return $this->redirectToRoute('cart');
        }

        $sessionCart = $cartService->getCart(); // récupère le panier en session
        $stripeCart = []; // initialise un panier Stripe (qui sera envoyé à Stripe)

        foreach ($sessionCart as $line) { // transforme le panier session en panier Stripe
            $stripeElement = [ // produit tel que Stripe en a besoin pour le traiter, les noms des index sont importants
                'quantity' => $line['quantity'],
                'price_data' => [
                    'currency' => 'EUR',
                    'unit_amount' => $line['product']->getPrice() * 100,
                    'product_data' => [
                        'name' => $line['product']->getName(),
                        // 'description' => $line['product']->getDescription(),
                        // 'images' => [
                        //     'https://127.0.0.1:8000/public/img/product/' . $line['product']->getImg1()
                        // ]
                    ]
                ]
            ];
            $stripeCart[] = $stripeElement;
        }

        $carrier = $order->getCarrier();
        $stripeElement = [ // transforme le transporteur en produit
            'quantity' => 1,
            'price_data' => [
                'currency' => 'EUR',
                'unit_amount' => $carrier->getPrice() * 100,
                'product_data' => [
                    'name' => $carrier->getName(),
                ]
            ]
        ];
        $stripeCart[] = $stripeElement;

        $stripe = new StripeClient($this->getParameter('stripe_secret_key')); // initialise Stripe avec la clé secrète

        $stripeSession = $stripe->checkout->sessions->create([ // crée la session de paiement Stripe
            'line_items' => $stripeCart,
            'mode' => 'payment',
            'success_url' => 'https://127.0.0.1:8000/payment/' . $order->getId() . '/success',
            'cancel_url' => 'https://127.0.0.1:8000/payment/cancel',
            'payment_method_types' => ['card']
        ]);

        return $this->render('payment/index.html.twig', [
            'sessionId' => $stripeSession->id
        ]);
    }

    #[Route('/payment/{order}/success', name: 'payment_success')]
    public function success(CartService $cartService, Order $order, ManagerRegistry $managerRegistry)
    {
        // vérifier qu'on vient bien de la page de paiemetn Stripe

        $cartService->clear(); // vide le panier

        $order->setPaid(true); // passe la commande à setPaid(true)
        $managerRegistry->getManager()->persist($order);
        $managerRegistry->getManager()->flush();

        // envoyer un mail récapitulatif au client
        // envoyer un mail d'information à l'admin (préparation de la commande)

        // gestion du stock produit en base de données
        
        return $this->render('payment/success.html.twig');
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function cancel()
    {
        // vérifier qu'on vient bien de la page de paiemetn Stripe
        return $this->render('payment/cancel.html.twig');
    }
}
