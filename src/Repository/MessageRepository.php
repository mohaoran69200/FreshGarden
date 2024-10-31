<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Message::class);
    }

    public function findReceivedMessagesPaginated(int $userId, int $page = 1, int $limit = 10): PaginationInterface
    {
        // Créer une requête pour obtenir les messages reçus par l'utilisateur spécifié
        $qb = $this->createQueryBuilder('m')
            ->where('m.recipient = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.createdAt', 'DESC'); // Trier par date de création (du plus récent au plus ancien)

        // Appliquer la pagination
        return $this->paginator->paginate($qb, $page, $limit);
    }

    public function findSentMessagesPaginated(int $userId, int $page = 1, int $limit = 10): PaginationInterface
    {
        // Créer une requête pour obtenir les messages envoyés par l'utilisateur spécifié
        $qb = $this->createQueryBuilder('m')
            ->where('m.sender = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.createdAt', 'DESC'); // Trier par date de création (du plus récent au plus ancien)

        // Appliquer la pagination
        return $this->paginator->paginate($qb, $page, $limit);
    }


}

