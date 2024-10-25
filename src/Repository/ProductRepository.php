<?php

namespace App\Repository;

use App\DTO\SearchDto;
use App\Entity\Product;
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

    public function search(SearchDto $search, int $page = 1, int $limit = 12): PaginationInterface
    {
        // Créer la requête de base pour l'entité Product
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('u.userProfile', 'up');

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

        return $this->paginator->paginate($qb, $page, $limit);
    }

    public function findByCategoryPaginated($category, int $page = 1, int $limit = 12): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.categorie = :categorie')
            ->setParameter('categorie', $category);

        return $this->paginator->paginate($qb, $page, $limit);
    }

    public function findAllPaginatedForAdmin(int $page = 1, int $limit = 20): PaginationInterface
    {
        // Création de la requête de base pour récupérer tous les produits
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC'); // Tri des produits par ID, vous pouvez adapter selon vos besoins

        // Utilisation de la pagination avec KnpPaginatorBundle
        return $this->paginator->paginate($qb, $page, $limit);
    }
}

