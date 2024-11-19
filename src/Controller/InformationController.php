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
        // Créez un nouveau message ou utilisez une entité existante
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        // Gérez la soumission du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, enregistrez-le ou effectuez une action
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new LogicException('L\'utilisateur authentifié n\'est pas valide.');
            }
            $message->setSender($user);
            $entityManager->persist($message);
            $entityManager->flush();

            // Ajoutez un message flash pour notifier l'utilisateur
            $this->addFlash('message', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('message_index');
        }

        // Passer le formulaire au template
        return $this->render('information/contact.html.twig', [
            'form' => $form->createView(),
            'adminId' => 1,
        ]);
    }


    #[Route('/conditions-generales-utilisation', name: 'cgu')]
    public function cgu(): Response
    {
        return $this->render('information/cgu.html.twig');
    }

    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('information/mentions_legales.html.twig');
    }

    #[Route('/conditions-generales-ventes', name: 'cgv')]
    public function cgv(): Response
    {
        return $this->render('information/cgv.html.twig');
    }

    #[Route('/a-propos', name: 'a_propos')]
    public function aPropos(): Response
    {
        return $this->render('information/a_propos.html.twig');
    }
}
