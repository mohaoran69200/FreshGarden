<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirige l'utilisateur s'il est déjà connecté
        $user = $this->getUser();
        if ($user) {
            // Vérification directe des rôles
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('app_admin_dashboard'); // Redirection vers le dashboard admin
            } elseif (in_array('ROLE_USER', $user->getRoles())) {
                return $this->redirectToRoute('profile'); // Redirection vers le profil de l'utilisateur
            }
            return $this->redirectToRoute('home'); // Redirection vers la page d'accueil par défaut
        }

        // Récupère les erreurs d'authentification et le dernier nom d'utilisateur
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide - elle est interceptée par le système de sécurité de Symfony.
        throw new \LogicException('Cette méthode peut rester vide - elle est interceptée par le système de sécurité de Symfony.');
    }
}
