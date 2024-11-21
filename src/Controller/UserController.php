<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\EditAdressType;
use App\Form\EditEmailType;
use App\Form\EditPersonalInfoType;
use App\Form\EditPhoneNumberType;
use App\Form\EditPasswordType;
use App\Form\ImageUserType;
use App\Form\RoleType;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/user', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('/show/{id}', name: 'show', methods: ['GET'])]
    public function show(
        User $user,
        FavoriteRepository $favoriteRepository
    ): Response {
        // Récupérer l'utilisateur actuellement connecté
        $currentUser = $this->getUser();

        // Initialiser la variable $isFavorite pour l'utilisateur consulté
        $isFavorite = false;

        // Tableau pour stocker l'état de favori pour chaque produit de l'utilisateur consulté
        $productFavorites = [];

        if ($currentUser) {
            // Vérifier si l'utilisateur consulté est dans les favoris de l'utilisateur connecté
            $favorite = $favoriteRepository->findOneBy([
                'user' => $currentUser,
                'userFavorite' => $user
            ]);
            $isFavorite = $favorite !== null;

            // Récupérer les produits favoris de l'utilisateur connecté
            $favorites = $favoriteRepository->findBy(['user' => $currentUser]);

            // Créer un tableau associant chaque produit de l'utilisateur consulté à son état de favori
            foreach ($user->getProducts() as $product) {
                $productFavorites[$product->getId()] = false; // Par défaut, le produit n'est pas en favori
            }
            foreach ($favorites as $favorite) {
                if (
                    $favorite->getProductFavorite()
                    && array_key_exists($favorite->getProductFavorite()->getId(), $productFavorites)
                ) {
                    $productFavorites[$favorite->getProductFavorite()->getId()] = true;
                }
            }
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,                     // Utilisateur dont on consulte le profil
            'isFavorite' => $isFavorite,         // Est-il dans les favoris de l'utilisateur connecté ?
            'productFavorites' => $productFavorites, // État des favoris pour chaque produit de l'utilisateur consulté
        ]);
    }


    #[Route('/edit-user/{id}', name: 'edit_user', methods: ['GET', 'POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $currentUser = $this->getUser();

        // Comparer les identifiants des utilisateurs pour éviter de modifier un profil qui n'est pas à soi,
        // sauf si l'utilisateur a le rôle d'ADMIN
        if (
            !$currentUser instanceof User ||
            ($currentUser->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN'))
        ) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        // Je récupère le profil utilisateur
        $profile = $user->getUserProfile();

        // Si l'utilisateur n'a pas de profil, en créer un nouveau
        if ($profile === null) {
            $profile = new UserProfile();
            $profile->setUser($user);
            $entityManager->persist($profile);
            $entityManager->flush();
        }

        // Formulaires pour les informations personnelles et l'adresse
        $personalForm = $this->createForm(EditPersonalInfoType::class, $profile);
        $addressForm = $this->createForm(EditAdressType::class, $profile);

        $personalForm->handleRequest($request);
        $addressForm->handleRequest($request);

        // Formulaire pour l'image
        $imageForm = $this->createForm(ImageUserType::class, $profile);
        $imageForm->handleRequest($request);

        // Gestion de l'upload d'image
        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $imageFile = $imageForm->get('imageFile')->getData();
            if ($imageFile) {
                $profile->setImageFile($imageFile);
                $entityManager->flush();
                $this->addFlash('success', 'Votre photo de profil a été mise à jour.');
            }
        }

        // Vérification des formulaires soumis et valides
        if ($personalForm->isSubmitted() && $personalForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Vos informations personnelles ont été mises à jour.');
        }

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre adresse a été mise à jour.');
        }

        $roleForm = $this->createForm(RoleType::class, $user);
        $roleForm->handleRequest($request);

        if ($roleForm->isSubmitted() && $roleForm->isValid()) {
            $data = $roleForm->getData();
            $user->setRoles($data->getRoles());
            $entityManager->flush();
            $this->addFlash('success', 'Le rôle de l\'utilisateur a été modifié avec succès');
        }

        // Rendre le template Twig avec toutes les informations nécessaires
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'personalForm' => $personalForm->createView(),
            'addressForm' => $addressForm->createView(),
            'imageForm' => $imageForm->createView(),
            'roleForm' => $roleForm->createView(),
        ]);
    }


    #[Route('/edit-user/edit-password/{id}', name: 'edit_user_password', methods: ['GET', 'POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
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


    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/edit-user/edit-contact/{id}', name: 'edit_user_contact', methods: ['GET', 'POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
    public function editContact(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        // Vérifier si l'utilisateur connecté est bien l'utilisateur que l'on veut modifier
        $currentUser = $this->getUser();
        if (!($currentUser instanceof User)) {
            // Gérer le cas où l'utilisateur n'est pas connecté
            throw new AccessDeniedException();
        }

        if ($currentUser->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        // Formulaire d'édition du contact
        $form = $this->createForm(EditEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Envoi du mail
            $email = (new Email())
                ->from($user->getEmail())
                ->to('support@example.com') // Destinataire
                ->subject('Demande de modification de contact')
                ->text('Une demande de modification de vos informations a été effectuée.');

            $mailer->send($email);

            // Mise à jour de l'utilisateur
            $entityManager->flush();

            $this->addFlash('success', 'Votre contact a été mis à jour.');

            return $this->redirectToRoute('home');
        }

        return $this->render('user/edit_contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/confirm-new-email/{token}', name: 'confirm_email', methods: ['GET'])]
    public function confirmNewEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        // Je recherche l'utilisateur par le token
        $user = $entityManager->getRepository(User::class)->findOneBy(['emailToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Token invalide ou expiré.');
            return $this->redirectToRoute('home');
        }

        // Je mets à jour l'email de l'utilisateur
        $user->setEmail($user->getEmailTemporary());
        $user->setEmailTemporary(null); // Remise à zéro de l'email temporaire
        $user->setToken(null); // Suppression du token
        $entityManager->flush();

        $this->addFlash('success', 'Votre email a bien été confirmé.');
        return $this->redirectToRoute('home');
    }

    #[Route('/update-image', name: 'update_image', methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
    public function updateImage(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        // Je m'assure que l'utilisateur est valide
        if (!$user instanceof User) {
            throw new AccessDeniedException('L\'utilisateur connecté n\'est pas valide.');
        }

        $profile = $user->getUserProfile();

        if (!$profile) {
            return new JsonResponse(['error' => 'User profile not found.'], 404);
        }

        // Je traite l'upload de l'image
        if ($request->files->has('image')) {
            $imageFile = $request->files->get('image');
            $imageName = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/user_profile', $imageName);

            $profile->setImageName($imageName);
            $entityManager->persist($profile);
            $entityManager->flush();

            return new JsonResponse(['imageUrl' => '/uploads/' . $imageName]);
        }

        return new JsonResponse(['error' => 'No image uploaded.'], 400);
    }

    #[Route('/delete-image', name: 'delete_image', methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
    public function deleteImage(
        Request $request,
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authChecker
    ): Response {
        // Je récupère l'utilisateur connecté
        $user = $this->getUser();
        if (!$user || !$authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('L\'utilisateur connecté n\'est pas valide.');
        }

        $profile = $user->getUserProfile();

        if (!$profile) {
            throw $this->createNotFoundException('User profile not found.');
        }

        // Je vérifie le token CSRF
        if (!$this->isCsrfTokenValid('delete_image' . $profile->getId(), $request->request->get('_token'))) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }

        // Je récupère le chemin de l'image
        $imagePath = $this->getParameter(
            'kernel.project_dir'
        ) . '/public/uploads/user_profile' . $profile->getImageName();

        // Je supprime le fichier physique si il existe
        if ($profile->getImageName() && file_exists($imagePath)) {
            unlink($imagePath); // Suppression du fichier
        }

        // Je retire l'image du profil
        $profile->setImageName(null);
        $entityManager->persist($profile);
        $entityManager->flush();

        // Je mets un message flash et je redirige
        $this->addFlash('success', 'L\'image de profil a été supprimée avec succès.');

        return $this->redirectToRoute('app_user_edit_user', ['id' => $user->getId()]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
    public function remove(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        $currentUser = $this->getUser();

        // Je vérifie si l'utilisateur peut supprimer son propre compte ou si l'admin peut supprimer n'importe quel compte
        if (!$currentUser || ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            $this->addFlash('danger', 'Vous ne pouvez supprimer que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        // Si l'admin supprime un utilisateur, il ne doit pas se déconnecter lui-même
        if ($currentUser === $user) {
            $request->getSession()->invalidate();
            $tokenStorage->setToken(null);
        }

        // Je supprime l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('home');
    }
}
