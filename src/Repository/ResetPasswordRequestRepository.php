<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use DateTimeInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ResetPasswordRequest;

/**
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    // Persiste une nouvelle demande de réinitialisation de mot de passe
    public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->persist($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    // Supprime les demandes de réinitialisation de mot de passe expirées
    public function removeExpiredResetPasswordRequests(): int
    {
        $qb = $this->createQueryBuilder('r');
        return $qb->delete()
            ->where('r.expiresAt < :now')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->execute();
    }

    // Récupère l'identifiant de l'utilisateur associé à la demande de réinitialisation
    public function getUserIdentifier(object $user): string
    {
        return $user->getEmail();
    }

    // Supprime une demande de réinitialisation spécifique
    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->remove($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    // Crée une nouvelle demande de réinitialisation de mot de passe
    public function createResetPasswordRequest(object $user, DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequest
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }

    // Trouve une demande de réinitialisation de mot de passe en fonction du token
    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequest
    {
        return $this->createQueryBuilder('r')
            ->where('r.selector = :selector')
            ->setParameter('selector', $selector)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Récupère la date de la demande de réinitialisation non expirée la plus récente
    public function getMostRecentNonExpiredRequestDate(object $user): ?DateTimeInterface
    {
        $result = $this->createQueryBuilder('r')
            ->select('r.expiresAt')
            ->where('r.user = :user')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new DateTime())
            ->orderBy('r.expiresAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['expiresAt'] : null;
    }
}
