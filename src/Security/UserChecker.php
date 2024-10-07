<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    // La méthode doit retourner "void" pour respecter l'interface
    public function checkPreAuth(UserInterface $user): void
    {
        // Si l'utilisateur est banni, on l'empêche de se connecter
        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            throw new CustomUserMessageAccountStatusException('Votre compte est banni.');
        }
    }

    // La méthode doit aussi retourner "void"
    public function checkPostAuth(UserInterface $user): void
    {
        // Cette méthode est utilisée après que l'utilisateur a été authentifié
        // Aucune vérification supplémentaire n'est nécessaire pour l'instant
    }
}
