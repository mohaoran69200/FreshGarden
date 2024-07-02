<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\FavoriteType;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userFavorite = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $productFavorite = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: FavoriteType::class)]
    private array $favoriteType = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUserFavorite(): ?User
    {
        return $this->userFavorite;
    }

    public function setUserFavorite(User $userFavorite): static
    {
        $this->userFavorite = $userFavorite;

        return $this;
    }

    public function getProductFavorite(): ?Product
    {
        return $this->productFavorite;
    }

    public function setProductFavorite(Product $productFavorite): static
    {
        $this->productFavorite = $productFavorite;

        return $this;
    }

    /**
     * @return FavoriteType[]
     */
    public function getFavoriteType(): array
    {
        return $this->favoriteType;
    }

    public function setFavoriteType(array $favoriteType): static
    {
        $this->favoriteType = $favoriteType;

        return $this;
    }
}
