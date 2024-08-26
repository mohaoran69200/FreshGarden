<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")] // Assure que seul un utilisateur connecté peut accéder à ce contrôleur
#[Route('/message', name: 'message_')] // Route de base pour les actions liées aux messages
class MessageController extends AbstractController
{
    private AuthorizationCheckerInterface $authChecker;

    // Injection du service d'autorisation dans le constructeur
    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        // Affiche la vue des messages (liste, etc.)
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }

    #[Route('/send/{id}', name: 'send')]
    public function send(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $recipient = $entityManager->getRepository(User::class)->find($id);
        if (!$recipient) {
            throw $this->createNotFoundException('Destinataire non trouvé.');
        }

        $message = new Message();
        $message->setRecipient($recipient);
        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définit l'expéditeur comme l'utilisateur actuellement connecté
            $message->setSender($this->getUser());

            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash("message", "Votre message a bien été envoyé.");
            return $this->redirectToRoute('message_index');
        }

        return $this->render('message/send.html.twig', [
            "form" => $form->createView(),
            'recipient' => $recipient,
        ]);
    }



    #[Route('/received', name: 'received')]
    public function received(): Response
    {
        $user = $this->getUser();
        // Récupère les messages reçus par l'utilisateur
        $messages = $user->getReceived(); // Utilise la méthode appropriée pour obtenir les messages reçus

        // Affiche les messages reçus dans la vue
        return $this->render('message/received.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/read/{id}', name: 'read')]
    public function read(
        EntityManagerInterface $entityManager,
        Message $message
    ): Response
    {
        // Vérifie si l'utilisateur a le droit de voir ce message
        $this->denyAccessUnlessGranted('view', $message);

        // Marque le message comme lu
        $message->setRead(true);

        $entityManager->persist($message); // Prépare la mise à jour du message
        $entityManager->flush(); // Effectue la mise à jour en base de données

        // Affiche le contenu du message dans la vue
        return $this->render('message/read.html.twig', ['message' => $message]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(
        EntityManagerInterface $entityManager,
        Message $message
    ): Response
    {
        // Vérifie si l'utilisateur a le droit de supprimer ce message
        $this->denyAccessUnlessGranted('delete', $message);

        $entityManager->remove($message); // Prépare la suppression du message
        $entityManager->flush(); // Effectue la suppression en base de données

        // Redirection vers la liste des messages après suppression
        return $this->redirectToRoute('message_index');
    }
}
