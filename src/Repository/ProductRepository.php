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

    public function search(SearchDto $search): array
    {
        // Créer la requête de base pour l'entité Product
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u') // Jointure avec l'entité User
            ->leftJoin('u.userProfile', 'up'); // Jointure avec l'entité UserProfile

        // Filtrage par nom du produit
        if ($search->getSearch()) {
            $qb->andWhere('p.name LIKE :search')
                ->setParameter('search', '%' . $search->getSearch() . '%');
        }

        // Filtrage par catégorie
        if ($search->getCategorie()) {
            $qb->andWhere('p.categorie = :categorie')
                ->setParameter('categorie', $search->getCategorie());
        }

        // Filtrage par ville à partir de UserProfile
        if ($search->getCity()) {
            $qb->andWhere('up.city LIKE :city')
                ->setParameter('city', '%' . $search->getCity() . '%'); // Recherche partielle sur la ville
        }

        // Filtrage par prix minimum
        if ($search->getMinPrice()) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $search->getMinPrice());
        }

        // Filtrage par prix maximum
        if ($search->getMaxPrice()) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $search->getMaxPrice());
        }

        // Retourner les résultats de la requête
        return $qb->getQuery()->getResult();
    }
}
