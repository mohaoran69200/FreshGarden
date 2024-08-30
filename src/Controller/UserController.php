<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use App\Form\EditPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/user', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('/edit-user/{id}', name: 'edit_user')]
    #[IsGranted('ROLE_USER')]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $currentUser = $this->getUser();

        if (!$currentUser || $currentUser !== $user) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Les informations de votre compte ont été mises à jour avec succès.');
            return $this->redirectToRoute('home');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/edit-user/edit-password/{id}', name: 'edit_user_password')]
    #[IsGranted('ROLE_USER')]
    public function editPassword(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $currentUser = $this->getUser();

        if (!$currentUser || $currentUser !== $user) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(EditPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('current_password')->getData();
            $newPassword = $form->get('new_password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            // Vérification de l'ancien mot de passe
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('danger', 'L\'ancien mot de passe est incorrect.');
            } elseif ($newPassword !== $confirmPassword) {
                $this->addFlash('danger', 'Le nouveau mot de passe et la confirmation ne correspondent pas.');
            } else {
                // Hachage et mise à jour du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'show')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        $currentUser = $this->getUser();

        if (!$currentUser || $currentUser !== $user) {
            $this->addFlash('danger', 'Vous ne pouvez supprimer que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $request->getSession()->invalidate();
        $tokenStorage->setToken(null);

        return $this->redirectToRoute('home');
    }

    #[Route('/update-image', name: 'update_image', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateImage(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $profile = $user->getUserProfile();
        if (!$profile) {
            return new JsonResponse(['error' => 'User profile not found.'], 404);
        }

        if ($request->files->has('image')) {
            $imageFile = $request->files->get('image');
            $imageName = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/', $imageName);

            $profile->setImageName($imageName);
            $entityManager->persist($profile);
            $entityManager->flush();

            return new JsonResponse(['imageUrl' => '/uploads/' . $imageName]);
        }

        return new JsonResponse(['error' => 'No image uploaded.'], 400);
    }

    #[Route('/delete-image', name: 'delete_image', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteImage(
        Request $request,
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authChecker
    ): Response {
        $user = $this->getUser();
        if (!$user || !$authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $profile = $user->getUserProfile();
        if (!$profile) {
            throw $this->createNotFoundException('User profile not found.');
        }

        if (!$this->isCsrfTokenValid('delete_image' . $profile->getId(), $request->request->get('_token'))) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }

        $profile->setImageName(null);
        $entityManager->persist($profile);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_edit_user', ['id' => $user->getId()]);
    }
}
