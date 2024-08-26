<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Message;

class MessageVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    // Cette méthode détermine si le voter peut gérer l'attribut et le sujet donnés
    protected function supports(string $attribute, $subject): bool
    {
        // Vérifie que l'attribut est supporté et que le sujet est une instance de Message
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Message;
    }

    // Cette méthode détermine si l'utilisateur est autorisé à effectuer l'action sur le sujet
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Vérifie que l'utilisateur est authentifié
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Message $message */
        $message = $subject;

        switch ($attribute) {
            case self::VIEW:
                // L'utilisateur peut voir le message s'il est l'expéditeur ou le destinataire
                return $user === $message->getSender() || $user === $message->getRecipient();

            case self::EDIT:
                // L'utilisateur peut éditer le message s'il en est l'expéditeur
                return $user === $message->getSender();

            case self::DELETE:
                // L'utilisateur peut supprimer le message s'il en est l'expéditeur
                return $user === $message->getSender();
        }

        // Retourne false par défaut si l'attribut n'est pas reconnu
        return false;
    }
}
