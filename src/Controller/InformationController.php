<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/information', name: 'app_information_')]
class InformationController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée un nouveau message ou utilise une entité existante
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        // Gère la soumission du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, enregistre-le ou effectue une action
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new LogicException('L\'utilisateur authentifié n\'est pas valide.');
            }
            // Associe l'utilisateur authentifié au message
            $message->setSender($user);
            $entityManager->persist($message);
            $entityManager->flush();

            // Ajoute un message flash pour notifier l'utilisateur
            $this->addFlash('message', 'Votre message a bien été envoyé.');

            // Redirige vers la page des messages
            return $this->redirectToRoute('message_index');
        }

        // Passe le formulaire au template
        return $this->render('information/contact.html.twig', [
            'form' => $form->createView(),
            'adminId' => 1,
        ]);
    }

    #[Route('/conditions-generales-utilisation', name: 'cgu')]
    public function cgu(): Response
    {
        // Rendu de la page CGU
        return $this->render('information/cgu.html.twig');
    }

    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(): Response
    {
        // Rendu de la page Mentions légales
        return $this->render('information/mentions_legales.html.twig');
    }

    #[Route('/conditions-generales-ventes', name: 'cgv')]
    public function cgv(): Response
    {
        // Rendu de la page CGV
        return $this->render('information/cgv.html.twig');
    }

    #[Route('/a-propos', name: 'a_propos')]
    public function aPropos(): Response
    {
        // Rendu de la page À propos
        return $this->render('information/a_propos.html.twig');
    }
}
