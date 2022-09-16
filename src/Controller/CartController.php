<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Service\CartService;
use App\Form\CartValidationType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cartService->getCart(),
            'total' => $cartService->getTotal()
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(CartService $cartService, int $id, Request $request): Response
    {
        $cartService->add($id);
        $this->addFlash('success', 'Le produit a bien été ajouté au panier');
        if ($request->headers->get('referer') === 'https://127.0.0.1:8000/cart') {
            return $this->redirectToRoute('cart');
        } else {
            return $this->redirectToRoute('products');
        }
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(CartService $cartService, int $id): Response
    {
        $cartService->remove($id);
        $this->addFlash('success', 'Le nombre de produits a été mis à jour');
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/delete/{id}', name: 'cart_delete')]
    public function delete(CartService $cartService, int $id): Response
    {
        $cartService->delete($id);
        $this->addFlash('success', 'Le produit a bien été supprimé du panier');
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Le panier a bien été vidé');
        return $this->redirectToRoute('cart');
    }

    public function getNbProducts(CartService $cartService): Response
    {
        return $this->render('cart/nbProducts.html.twig', [
            'nbProducts' => $cartService->getNbProducts()
        ]);
    }

    #[Route('/cart/validation', name: 'cart_validation')]
    public function validation(Request $request, CartService $cartService, ManagerRegistry $managerRegistry): Response
    {
        $cartValidationFrom = $this->createForm(CartValidationType::class);
        $cartValidationFrom->handleRequest($request);

        if ($cartValidationFrom->isSubmitted() && $cartValidationFrom->isValid()) {

            $manager = $managerRegistry->getManager();
            $carrier = $cartValidationFrom['carrier']->getData();

            $order = new Order(); // génère une nouvelle commande (pour la BDD)
            $order
                ->setUser($this->getUser())
                ->setDeliveryAddress($cartValidationFrom['delivery_address']->getData())
                ->setBillingAddress($cartValidationFrom['billing_address']->getData())
                ->setCarrier($carrier)
                ->setReference('O' . date_format(new \DateTime(), 'Ymdhis'))
                ->setAmount($cartService->getTotal() + $carrier->getPrice())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setPaid(false)
            ;

            $manager->persist($order);

            foreach ($cartService->getCart() as $line) {
                $orderDetail = new OrderDetail();
                $orderDetail
                    ->setOrderId($order)
                    ->setProductId($line['product'])
                    ->setQuantity($line['quantity'])
                ;
                $manager->persist($orderDetail);
            }

            $manager->flush();

            return $this->redirectToRoute('payment', [
                'order' => $order->getId()
            ]);

        }

        return $this->render('cart/validation.html.twig', [
            'cart' => $cartService->getCart(),
            'total' => $cartService->getTotal(),
            'cartValidationForm' => $cartValidationFrom->createView()
        ]);
    }
}
