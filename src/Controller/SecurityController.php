<?php

namespace App\Controller;

use App\Entity\User;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private TokenStorageInterface $tokenStorage;
    private AuthenticationUtils $authenticationUtils;

// Injecter AuthenticationUtils
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationUtils $authenticationUtils)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationUtils = $authenticationUtils;
    }

    #[Route(path: '/login', name: 'login')]
    public function login(SessionInterface $session): Response
    {
        $session->set('login_origin', true);

// Vérifiez si l'utilisateur est connecté
        $user = $this->getUser();

// Si l'utilisateur est déjà connecté, redirigez vers la page d'accueil
        if ($user instanceof User) {
// Si l'utilisateur n'est pas vérifié, déconnectez-le
            if (!$user->isVerified()) {
// Déconnecter l'utilisateur
                $this->tokenStorage->setToken(null);  // Déconnecte l'utilisateur
                $this->addFlash('warning', 'Votre compte n\'est pas activé. Vous allez être déconnecté.');
                return $this->redirectToRoute('logout');  // Redirection vers la page de déconnexion
            }

// Si l'utilisateur est connecté et validé, redirigez-le vers la page d'accueil
            return $this->redirectToRoute('home');
        }

// Récupérer l'email saisi lors de la dernière tentative de connexion
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $error = $this->authenticationUtils->getLastAuthenticationError();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
