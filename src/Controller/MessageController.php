<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
#[Route('/message', name: 'message_')]
class MessageController extends AbstractController
{
    private AuthorizationCheckerInterface $authChecker;

    // Je crée un constructeur pour initialiser l'AuthorizationChecker
    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    // J'affiche la page principale des messages
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        // Je redirige l'utilisateur vers la page de connexion s'il n'est pas connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }

    // Je gère l'envoi d'un message à un utilisateur spécifique
    #[Route('/send/{id}', name: 'send')]
    public function send(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
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
            $message->setSender($this->getUser());

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

    // J'affiche les messages reçus par l'utilisateur connecté
    #[Route('/received', name: 'received')]
    public function received(): Response
    {
        // Je récupère l'utilisateur connecté
        $user = $this->getUser();

        // Je récupère les messages reçus par cet utilisateur
        $messages = $user->getReceived();

        return $this->render('message/received.html.twig', [
            'messages' => $messages,
        ]);
    }

    // Je marque un message comme lu et affiche son contenu
    #[Route('/read/{id}', name: 'read')]
    public function read(
        EntityManagerInterface $entityManager,
        Message $message): Response
    {
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
        Message $message): Response
    {
        // Je m'assure que l'utilisateur a les droits pour supprimer ce message
        $this->denyAccessUnlessGranted('delete', $message);

        // Je supprime le message de la base de données
        $entityManager->remove($message);
        $entityManager->flush();

        // Je redirige vers la page principale des messages après suppression
        return $this->redirectToRoute('message_index');
    }
}
