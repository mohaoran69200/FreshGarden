<?php

namespace App\Repository;

use App\DTO\SearchDto;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function search(SearchDto $search): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($search->getSearch()) {
            $qb->andWhere('p.name LIKE :search')
                ->setParameter('search', '%' . $search->getSearch() . '%');
        }
        if ($search->getCategorie()) {
               $qb->andWhere('p.categorie = :categorie')
                   ->setParameter('categorie', $search->getCategorie());
        }
        if ($search->getMinPrice()) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $search->getMinPrice());
        }
        if ($search->getMaxPrice()) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $search->getMaxPrice());
        }


        return $qb->getQuery()->getResult();
    }
}
