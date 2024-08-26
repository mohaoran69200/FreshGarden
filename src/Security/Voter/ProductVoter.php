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

    protected function supports(string $attribute, $subject): bool
    {
        // Le sujet doit être une instance de Product
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Product;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Vérifiez que l'utilisateur est authentifié
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Product $product */
        $product = $subject;

        switch ($attribute) {
            case self::VIEW:
                // Tout le monde peut voir le produit
                return true;

            case self::EDIT:
                // Seul le propriétaire du produit peut le modifier
                return $user === $product->getUser();

            case self::DELETE:
                // Seul le propriétaire du produit peut le supprimer
                return $user === $product->getUser();
        }

        return false;
    }
}
