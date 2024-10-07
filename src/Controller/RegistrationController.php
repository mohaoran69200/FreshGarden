<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    // Gestion de l'inscription d'un nouvel utilisateur
    #[Route('/register', name: 'register')]
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager): Response
    {
        // Je crée un nouvel utilisateur et génère le formulaire d'inscription
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis et validé, j'enregistre l'utilisateur
        if ($form->isSubmitted() && $form->isValid()) {
            // Je hache le mot de passe avant d'enregistrer l'utilisateur
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // J'enregistre le nouvel utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Après l'enregistrement, je redirige l'utilisateur vers la page de connexion
            return $this->redirectToRoute('login');
        }

        // Je retourne la vue avec le formulaire d'inscription s'il n'est pas soumis ou s'il contient des erreurs
        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
