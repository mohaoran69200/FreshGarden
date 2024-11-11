<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface; // Importez le PaginatorInterface
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private PaginatorInterface $paginator; // Déclarez la dépendance

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, User::class);
        $this->paginator = $paginator; // Injectez la dépendance dans le constructeur
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Returns a paginated list of users.
     *
     * @param int $page The page number (default 1)
     * @param int $limit The number of results per page (default 20)
     *
     * @return PaginationInterface<int, User> The paginated result
     */
    public function findPaginatedUsers(int $page = 1, int $limit = 20): PaginationInterface
    {
        // Créez la requête pour récupérer les utilisateurs
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        // Utilisez le paginator pour paginer les résultats
        return $this->paginator->paginate(
            $query, // La requête
            $page,  // Le numéro de la page
            $limit  // Le nombre d'éléments par page
        );
    }
}

