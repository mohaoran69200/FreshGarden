<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditAdressType;
use App\Form\EditEmailType;
use App\Form\EditPersonalInfoType;
use App\Form\EditPhoneNumberType;
use App\Form\EditUserType;
use App\Form\EditPasswordType;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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

        $profile = $user->getUserProfile();

        $personalForm = $this->createForm(EditPersonalInfoType::class, $profile);
        $addressForm = $this->createForm(EditAdressType::class, $profile);

        $personalForm->handleRequest($request);
        $addressForm->handleRequest($request);

        // Gestion de l'upload d'image
        $imageForm = $this->createFormBuilder()
            ->add('imageFile', FileType::class, [
                'label' => 'Changer la photo de profil',
                'required' => false,
            ])
            ->getForm();
        $imageForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $imageFile = $imageForm->get('imageFile')->getData();
            if ($imageFile) {
                $profile->setImageFile($imageFile);
                $entityManager->flush();
                $this->addFlash('success', 'Votre photo de profil a été mise à jour.');
            }
        }

        if ($personalForm->isSubmitted() && $personalForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Vos informations personnelles ont été mises à jour.');
        }

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre adresse a été mise à jour.');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'personalForm' => $personalForm->createView(),
            'addressForm' => $addressForm->createView(),
            'imageForm' => $imageForm->createView(),
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

    #[Route('/edit-user/edit-contact/{id}', name: 'edit_user_contact')]
    #[IsGranted('ROLE_USER')]
    public function editContact(Request $request,
                                User $user,
                                EntityManagerInterface $entityManager): Response {
        $user = $this->getUser();

        // Formulaire de modification d'email
        $emailForm = $this->createForm(EditEmailType::class, $user);
        $emailForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $oldEmail = $emailForm->get('old_email')->getData();
            $newEmail = $emailForm->get('new_email')->getData();

            // Vérifier si l'ancien email correspond à l'email actuel de l'utilisateur
            if ($oldEmail !== $user->getEmail()) {
                $this->addFlash('danger', 'L\'ancien email est incorrect.');
            } else {
                $user->setEmail($newEmail);
                $entityManager->flush();

                $this->addFlash('success', 'Votre email a bien été mis à jour.');
                return $this->redirectToRoute('app_user_edit_user');
            }
        }

        // Formulaire de modification du numéro de téléphone
        $phoneForm = $this->createForm(EditPhoneNumberType::class);
        $phoneForm->handleRequest($request);

        if ($phoneForm->isSubmitted() && $phoneForm->isValid()) {
            $oldPhone = $phoneForm->get('old_phone')->getData();
            $newPhone = $phoneForm->get('new_phone')->getData();

            // Vérifier si l'ancien numéro correspond à celui actuel de l'utilisateur
            if ($oldPhone !== $user->getUserProfile()->getPhoneNumber()) {
                $this->addFlash('danger', 'L\'ancien numéro de téléphone est incorrect.');
            } else {
                $user->getUserProfile()->setPhoneNumber($newPhone);
                $entityManager->flush();

                $this->addFlash('success', 'Votre numéro de téléphone a bien été mis à jour.');
                return $this->redirectToRoute('app_user_edit_user');
            }
        }
        return $this->render('user/edit_contact.html.twig', [
            'emailForm' => $emailForm->createView(),
            'phoneForm' => $phoneForm->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'show')]
    public function show(User $user, FavoriteRepository $favoriteRepository): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $currentUser = $this->getUser();

        // Initialiser la variable $isFavorite à false par défaut
        $isFavorite = false;

        // Si l'utilisateur est connecté, vérifier s'il a ajouté cet utilisateur (le profil affiché) à ses favoris
        if ($currentUser) {
            $favorite = $favoriteRepository->findOneBy([
                'user' => $currentUser,  // Utilisateur connecté (celui qui peut avoir des favoris)
                'userFavorite' => $user  // Utilisateur dont on consulte le profil
            ]);

            // Si un favori existe, $isFavorite devient true
            $isFavorite = $favorite !== null;
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,              // Utilisateur dont on consulte le profil
            'isFavorite' => $isFavorite,  // Est-il dans les favoris de l'utilisateur connecté ?
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
