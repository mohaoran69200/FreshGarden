<?php

namespace App\Repository;

use App\DTO\SearchDto;
use App\Entity\Product;
use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, public PaginatorInterface $paginator)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Recherche des produits avec filtres et pagination.
     *
     * @param SearchDto $search
     * @param int $page
     * @param int $limit
     * @return PaginationInterface<int, Product>
     */
    public function search(SearchDto $search, int $page = 1, int $limit = 12): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('u.userProfile', 'up');

        if ($search->getSearch()) {
            $qb->andWhere('p.name LIKE :search')
                ->setParameter('search', '%' . $search->getSearch() . '%');
        }

        if ($search->getCategorie()) {
            $qb->andWhere('p.categorie = :categorie')
                ->setParameter('categorie', $search->getCategorie());
        }

        if ($search->getCity()) {
            $qb->andWhere('up.city LIKE :city')
                ->setParameter('city', '%' . $search->getCity() . '%');
        }

        if ($search->getMinPrice()) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $search->getMinPrice());
        }

        if ($search->getMaxPrice()) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $search->getMaxPrice());
        }

        return $this->paginator->paginate($qb, $page, $limit);
    }

    /**
     * Récupère les produits par catégorie avec pagination.
     *
     * @param Categorie $category
     * @param int $page
     * @param int $limit
     * @return PaginationInterface<int, Product>
     */
    public function findByCategoryPaginated(Categorie $category, int $page = 1, int $limit = 12): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.categorie = :categorie')
            ->setParameter('categorie', $category);

        return $this->paginator->paginate($qb, $page, $limit);
    }

    /**
     * Récupère tous les produits pour l'admin avec pagination.
     *
     * @param int $page
     * @param int $limit
     * @return PaginationInterface<int, Product>
     */
    public function findAllPaginatedForAdmin(int $page = 1, int $limit = 20): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC');

        return $this->paginator->paginate($qb, $page, $limit);
    }
}
