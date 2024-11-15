<?php

namespace App\Security\Voter;

use App\Entity\Product;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    // Cette méthode détermine si le voter peut gérer l'attribut et le sujet donnés
    protected function supports(string $attribute, $subject): bool
    {
        // Le sujet doit être une instance de Product
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Product;
    }

    // Cette méthode détermine si l'utilisateur est autorisé à effectuer l'action sur le sujet
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Je vérifie que l'utilisateur est authentifié
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Product $product */
        $product = $subject;

        return match ($attribute) {
            self::VIEW => true,
            self::EDIT, self::DELETE => $user === $product->getUser() || in_array('ROLE_ADMIN', $user->getRoles()),
            default => false,
        };
    }
}

