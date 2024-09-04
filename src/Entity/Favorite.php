<?php

namespace App\Entity;

use App\Enum\FavoriteType;
use App\Repository\FavoriteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    private ?User $userFavorite = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    private ?Product $productFavorite = null;

    #[ORM\Column(type: Types::STRING, nullable: true, enumType: FavoriteType::class)]
    private ?FavoriteType $favoriteType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUserFavorite(): ?User
    {
        return $this->userFavorite;
    }

    public function setUserFavorite(?User $userFavorite): static
    {
        $this->userFavorite = $userFavorite;

        return $this;
    }

    public function getProductFavorite(): ?Product
    {
        return $this->productFavorite;
    }

    public function setProductFavorite(?Product $productFavorite): static
    {
        $this->productFavorite = $productFavorite;

        return $this;
    }

    public function getFavoriteType(): ?FavoriteType
    {
        return $this->favoriteType;
    }

    public function setFavoriteType(?FavoriteType $favoriteType): self
    {
        $this->favoriteType = $favoriteType;

        return $this;
    }
}
