<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            // Rediriger ou afficher un message d'erreur si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('login'); // Remplace par le nom de ta route de connexion
        }

        // Récupérer toutes les commandes de l'utilisateur connecté
        $orders = $orderRepository->findBy(['user' => $user]);


        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
}
