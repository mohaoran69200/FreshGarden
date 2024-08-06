<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\EditUserType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        if ($currentUser !== $user) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        $userProfile = $user->getUserProfile();
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        if ($currentUser !== $user) {
            $this->addFlash('danger', 'Vous ne pouvez supprimer que votre propre compte.');
            return $this->redirectToRoute('home');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $request->getSession()->invalidate();
        $tokenStorageInterface->setToken(null);

        return $this->redirectToRoute('home');
    }

    #[Route('/update-image', name: 'update_image', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateImage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $profile = $user->getUserProfile();
        if (!$profile) {
            return new JsonResponse(['error' => 'User profile not found.'], 404);
        }

        // Vérifier la présence de l'image dans la requête
        if ($request->files->has('image')) {
            $imageFile = $request->files->get('image');
            $imageName = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/', $imageName);

            // Mettre à jour le profil de l'utilisateur avec le nouvel image
            $profile->setImageFile($imageFile); // Vous pouvez également gérer le nom d'image si nécessaire
            $profile->setImageName($imageName);
            $entityManager->persist($profile);
            $entityManager->flush();

            return new JsonResponse(['imageUrl' => '/uploads/' . $imageName]);
        }

        return new JsonResponse(['error' => 'No image uploaded.'], 400);
    }

    #[Route('/delete-image', name: 'app_user_delete_image', methods: ['POST'])]
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

        $csrfToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_image' . $profile->getId(), $csrfToken)) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }

        // Supprimer l'image
        $profile->setImageFile(null);
        $profile->setImageName(null);
        $entityManager->persist($profile);
        $entityManager->flush();

        return $this->redirectToRoute('edit_user', ['id' => $user->getId()]);
    }
}
