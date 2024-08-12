<?php

namespace App\DTO;

use App\Entity\Categorie;
use Symfony\Component\Validator\Constraints as Assert;

class SearchDto
{
    private ?string $search = null;

    private ?Categorie $categorie = null;


    #[Assert\Positive()]
    #[Assert\LessThanOrEqual(propertyPath:'maxPrice')]
    private ?int $minPrice = null;

    #[Assert\Positive()]
    #[Assert\GreaterThanOrEqual(propertyPath:'minPrice')]
    private ?int $maxPrice = null;

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): void
    {
        $this->categorie = $categorie;
    }





/*    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }*/

    public function getMinPrice(): ?int
    {
        return $this->minPrice;
    }

    public function setMinPrice(?int $minPrice): void
    {
        $this->minPrice = $minPrice;
    }

    public function getMaxPrice(): ?int
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(?int $maxPrice): void
    {
        $this->maxPrice = $maxPrice;
    }
}