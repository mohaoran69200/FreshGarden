<?php

namespace App\Repository;

use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    public function findProductFavoriteForUser($user, $product)
    {
        return $this->findOneBy([
            'user' => $user,
            'productFavorite' => $product,
            'favoriteType' => 'product' // Assure-toi que ce type est correct
        ]);
    }
}
