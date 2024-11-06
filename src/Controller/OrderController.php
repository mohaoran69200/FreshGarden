<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\User;
use App\Enum\DeliveryMode;
use App\Enum\DeliveryStatus;
use App\Enum\OrderStatus;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    #[IsGranted('ROLE_USER')]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        // Récupérer toutes les commandes de l'utilisateur connecté
        $orders = $orderRepository->findBy(['user' => $user]);

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/order/new', name: 'app_order_new')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $entityManager, CartService $cartService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier s'il existe déjà une commande avec le statut ENATTENTE
        $existingOrder = $entityManager->getRepository(Order::class)->findOneBy([
            'user' => $user,
            'status' => OrderStatus::ENATTENTE
        ]);

        // Récupérer les produits du panier
        $cartItems = $cartService->getTotal();

        // Si une commande en attente existe, on l'utilise
        if ($existingOrder) {
            $order = $existingOrder;
        } else {
            // Si aucune commande en attente n'existe, créer une nouvelle commande
            $order = new Order();
            $order->setUser($user);
            $order->setStatus(OrderStatus::ENATTENTE);
            $order->setCreatedAt(new \DateTimeImmutable());

            // Calcul du total de la commande
            $total = 0;

            // Créez une nouvelle livraison uniquement si le mode est 'Livraison'
            $delivery = null;

            foreach ($cartItems as $item) {
                $orderLine = new OrderLine();
                $orderLine->setProduct($item['product']);
                $orderLine->setQuantity($item['quantity']);
                $order->addOrderLine($orderLine);

                // Calcul du prix total
                $total += $item['product']->getPrice() * $item['quantity'];

                // Ajoutez la ligne de commande à la commande
                $entityManager->persist($orderLine);
            }

            // Définir le total de la commande
            $order->setTotal($total);

            // Persister la livraison si elle a été créée
            if ($delivery) {
                $entityManager->persist($delivery);
            }

            // Persistez la commande
            $entityManager->persist($order);

            // Exécutez flush pour enregistrer les entités dans la base de données
            $entityManager->flush();
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        // Vérifier si le bouton "Annuler" est cliqué
        if ($request->request->get('cancel')) {
            return $this->redirectToRoute('app_order_cancel', ['id' => $order->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($order->getDeliveryMode() === DeliveryMode::Livraison) {
                if (!$user->getUserProfile()->getAddress()) {
                    $form->addError(new FormError(
                        'Vous devez renseigner une addresse sur votre compte pour pouvoir commander en livraison'
                    ));
                }
            }
            if ($form->isValid()) {
                if ($order->getDeliveryMode() === DeliveryMode::Livraison) {
                    $delivery = new Delivery();
                    $delivery->setAddress($user->getUserProfile()->getAddress());
                    $delivery->setCommand($order);
                    $delivery->setStatus(DeliveryStatus::ENATTENTE);
                }
                $order->setStatus(OrderStatus::CONFIRME);
                $cartService->removeCartAll();
                $entityManager->flush();
                // Si le formulaire est validé, on affiche un message de succès
                $this->addFlash('success', 'Votre commande a été mise à jour avec succès.');
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('order/new.html.twig', [
            'form' => $form->createView(),
            'order' => $order
        ]);
    }

    #[Route('/order/cancel/{id}', name: 'app_order_cancel')]
    #[IsGranted('ROLE_USER')]
    public function cancel(Order $order, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est bien celui qui a passé la commande
        $user = $this->getUser();
        if ($order->getUser() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler une commande qui ne vous appartient pas.');
            return $this->redirectToRoute('app_order');
        }

        // Mettre à jour le statut de la commande à 'ANNULEE'
        $order->setStatus(OrderStatus::ANNULEE);

        // Persist la mise à jour
        $entityManager->flush();

        $this->addFlash('success', 'Votre commande a été annulée.');

        // Rediriger vers la liste des commandes de l'utilisateur
        return $this->redirectToRoute('home');
    }
}
