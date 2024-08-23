<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/message', name: 'message_')]
class MessageController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('send.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }

    #[Route('/send', name: 'send')]
    public function send(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $message->setSender($this->getUser());

            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash("message", "Votre message a bien été envoyé.");
            return $this->redirectToRoute('message_index'); // Corrected route
        }

        return $this->render('message/send.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    #[Route('/received', name: 'received')]
    public function received(): Response
    {
        // Assuming you want to display messages received by the user
        $user = $this->getUser();
        $messages = $user->getReceivedMessages(); // Assumes there's a method for this

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
        // Mark message as read
        $message->setRead(true); // Assuming you have a setIsRead() method

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->render('message/read.html.twig', ['message' => $message]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(
        EntityManagerInterface $entityManager,
        Message $message
    ): Response
    {
        $entityManager->remove($message);
        $entityManager->flush();

        return $this->redirectToRoute('message_index'); // Redirect to received messages
    }
}
