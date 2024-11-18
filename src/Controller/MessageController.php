<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
#[Route('/message', name: 'message_')]
class MessageController extends AbstractController
{
    // J'affiche la page principale des messages
    // Affiche la page principale des messages
    #[Route('/', name: 'index')]
    public function index(MessageRepository $messageRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        // Récupérer les 5 derniers messages reçus
        $receivedMessages = $messageRepository->findBy(
            ['recipient' => $this->getUser()],
            ['createdAt' => 'DESC'],
            5
        );

        // Récupérer les 5 derniers messages envoyés
        $sentMessages = $messageRepository->findBy(
            ['sender' => $this->getUser()],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('message/index.html.twig', [
            'receivedMessages' => $receivedMessages,
            'sentMessages' => $sentMessages,
        ]);
    }


    // Je gère l'envoi d'un message à un utilisateur spécifique
    #[Route('/send/{id}', name: 'send')]
    public function send(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Je récupère le destinataire à partir de son ID
        $recipient = $entityManager->getRepository(User::class)->find($id);
        if (!$recipient) {
            throw $this->createNotFoundException('Destinataire non trouvé.');
        }

        // Je crée un nouveau message et l'associe au destinataire
        $message = new Message();
        $message->setRecipient($recipient);
        // Je crée le formulaire pour envoyer un message
        $form = $this->createForm(MessageType::class, $message);

        // Je gère la soumission du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, j'enregistre le message
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new LogicException('L\'utilisateur authentifié n\'est pas valide.');
            }
            $message->setSender($user);

            $entityManager->persist($message);
            $entityManager->flush();

            // Je notifie l'utilisateur que le message a été envoyé
            $this->addFlash("message", "Votre message a bien été envoyé.");
            return $this->redirectToRoute('message_index');
        }

        // Si le formulaire n'est pas soumis ou est invalide, je renvoie le formulaire avec la vue
        return $this->render('message/send.html.twig', [
            "form" => $form->createView(),
            'recipient' => $recipient,
        ]);
    }


    #[Route('/sent', name: 'sent')]
    public function sent(
        MessageRepository $messageRepository,
        Request $request,
        PaginatorInterface $pagination
    ): Response {
        $this->getUser();

        // Récupérer la page actuelle, par défaut 1 si aucune page n'est spécifiée
        $page = $request->query->getInt('page', 1);

        // Utiliser la pagination pour obtenir les messages envoyés
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new LogicException('L\'utilisateur authentifié n\'est pas valide.');
        }

        $messages = $messageRepository->findSentMessagesPaginated($user->getId(), $page);

        return $this->render('message/sent.html.twig', [
            'messages' => $messages,
            'pagination' => $pagination,
        ]);
    }


    #[Route('/edit/{id}', name: 'edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        Message $message
    ): Response {
        // Je vérifie que l'utilisateur a les droits pour éditer ce message
        $this->denyAccessUnlessGranted('edit', $message);

        // Je crée le formulaire pour modifier le message
        $form = $this->createForm(MessageType::class, $message);

        // Je gère la soumission du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, j'enregistre les modifications
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();

            // Je notifie l'utilisateur que le message a été modifié
            $this->addFlash("message", "Votre message a bien été modifié.");
            return $this->redirectToRoute('message_sent');
        }

        // Si le formulaire n'est pas soumis ou est invalide, je renvoie le formulaire avec la vue
        return $this->render('message/edit.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }


    // J'affiche les messages reçus par l'utilisateur connecté
    #[Route('/received', name: 'received')]
    public function received(MessageRepository $messageRepository, Request $request): Response
    {
        $this->getUser();

        // Récupérer la page actuelle, par défaut 1 si aucune page n'est spécifiée
        $page = $request->query->getInt('page', 1);

        // Utiliser la pagination pour obtenir les messages reçus
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new LogicException('L\'utilisateur authentifié n\'est pas valide.');
        }
        $messages = $messageRepository->findReceivedMessagesPaginated($user->getId(), $page);


        return $this->render('message/received.html.twig', [
            'messages' => $messages,
        ]);
    }


    // Je marque un message comme lu et affiche son contenu
    #[Route('/read/{id}', name: 'read')]
    public function read(
        EntityManagerInterface $entityManager,
        Message $message
    ): Response {
        // Je vérifie que l'utilisateur a bien les droits pour lire ce message
        $this->denyAccessUnlessGranted('view', $message);

        // Je marque le message comme lu
        $message->setRead(true);

        // J'enregistre cette modification en base de données
        $entityManager->persist($message);
        $entityManager->flush();

        // J'affiche la page du message avec son contenu
        return $this->render('message/read.html.twig', ['message' => $message]);
    }


    // Je supprime un message
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(
        EntityManagerInterface $entityManager,
        Message $message
    ): Response {
        // Je m'assure que l'utilisateur a les droits pour supprimer ce message
        $this->denyAccessUnlessGranted('delete', $message);

        // Je supprime le message de la base de données
        $entityManager->remove($message);
        $entityManager->flush();

        // Je redirige vers la page principale des messages après suppression
        return $this->redirectToRoute('message_index');
    }
}
