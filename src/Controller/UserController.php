<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\EditUserType;
use App\Form\EditUserProfileType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class UserController extends AbstractController
{
    #[Route('/edit-user/{id}', name: 'edit_user')]
    #[IsGranted('ROLE_USER')]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('home');
        }

        // Récupère le profil de l'utilisateur
        $userProfile = $user->getUserProfile();

        // Crée les formulaires pour l'utilisateur et le profil
        $form = $this->createForm(EditUserType::class, $user);

        // Traite les données soumises
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du mot de passe
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Les informations de votre compte ont été mises à jour avec succès.'
            );
            return $this->redirectToRoute('home');
        } else {
            $this->addFlash(
                'danger',
                'Le mot de passe renseigné est incorrect.'
            );
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
