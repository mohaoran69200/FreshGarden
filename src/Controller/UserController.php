<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserProfileRepository;
use App\Form\EditUserType;
use App\Form\EditUserProfileType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;


#[Route('/user', name: 'app_user_')]
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

        $currentUser = $this->getUser();

        // Vérifie si l'utilisateur est connecté
        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        // Vérifie si l'utilisateur connecté essaie de modifier son propre profil
        if ($currentUser !== $user) {
            // Optionnel : ajouter un message flash ou journaliser l'événement
            $this->addFlash('danger', 'Vous ne pouvez modifier que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        // Récupère le profil de l'utilisateur
        $userProfile = $user->getUserProfile();

        // Crée le formulaire pour l'utilisateur
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        // Traite les données du formulaire si soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du mot de passe
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Les informations de votre compte ont été mises à jour avec succès.');
            return $this->redirectToRoute('home');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'userProfile' => $userProfile,
        ]);
    }


    #[Route('/show/{id}', name: 'show')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorageInterface
    ): Response {
        $currentUser = $this->getUser();

        // Vérifie si l'utilisateur est connecté
        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        // Vérifie si l'utilisateur connecté essaie de supprimer son propre compte
        if ($currentUser !== $user) {
            // Optionnel : ajouter un message flash ou journaliser l'événement
            $this->addFlash('danger', 'Vous ne pouvez supprimer que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        // Supprime l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        // Invalide la session et déconnecte l'utilisateur
        $request->getSession()->invalidate();
        $tokenStorageInterface->setToken(null);

        return $this->redirectToRoute('home');
    }
}
