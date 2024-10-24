<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Product;
use App\Entity\Image;
use App\Enum\ProductUnit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use DateTimeImmutable;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $productsData = [
            'Fruits' => ['Pomme', 'Orange', 'Fraise', 'Datte', 'Melon', 'Framboise', 'Pêche', 'Poire', 'Raisin', 'Cerise', 'Kiwi'],
            'Legumes' => ['Carotte', 'Poivron', 'Tomate', 'Laitue', 'Epinard', 'Radis', 'Oignon', 'Haricot', 'Betterave', 'Chou'],
            'Autre' => ['Œuf de poule', 'Fromage', 'Miel', 'Menthe', 'Noisettes' ]
        ];

        $categories = [];

        foreach ($productsData as $categoryName => $products) {
            $categorie = new Categorie();
            $categorie->setName($categoryName);
            $manager->persist($categorie);
            $categories[$categoryName] = $categorie;
        }

        $imagePaths = [
            'Pomme' => 'public/uploads/product/pomme.jpg',
            'Orange' => 'public/uploads/product/orange.jpg',
            'Fraise' => 'public/uploads/product/fraise.jpg',
            'Datte' => 'public/uploads/product/datte.jpg',
            'Melon' => 'public/uploads/product/melon.jpg',
            'Framboise' => 'public/uploads/product/framboise.jpg',
            'Pêche' => 'public/uploads/product/peche.jpg',
            'Poire' => 'public/uploads/product/poire.jpg',
            'Raisin' => 'public/uploads/product/raisins.jpg',
            'Cerise' => 'public/uploads/product/cerise.jpg',
            'Kiwi' => 'public/uploads/product/kiwi.jpg',
            'Carotte' => 'public/uploads/product/carotte.jpg',
            'Poivron' => 'public/uploads/product/poivron.jpg',
            'Tomate' => 'public/uploads/product/tomate.jpg',
            'Laitue' => 'public/uploads/product/laitue.jpg',
            'Epinard' => 'public/uploads/product/epinard.jpg',
            'Radis' => 'public/uploads/product/radis.jpg',
            'Oignon' => 'public/uploads/product/oignon.jpg',
            'Haricot' => 'public/uploads/product/haricot.jpg',
            'Betterave' => 'public/uploads/product/betterave.jpg',
            'Chou' => 'public/uploads/product/chou.jpg',
            'Œuf de poule' => 'public/uploads/product/oeuf.jpg',
            'Fromage' => 'public/uploads/product/fromage.jpg',
            'Miel' => 'public/uploads/product/miel.jpg',
            'Menthe' => 'public/uploads/product/menthe.jpg',
            'Noisettes' => 'public/uploads/product/noisettes.jpg',
        ];

        $images = [];
        foreach ($imagePaths as $productName => $path) {
            $image = new Image();
            $image
                ->setName(basename($path))
                ->setUpdatedAt(new DateTimeImmutable());
            $manager->persist($image);
            $images[$productName] = $image;

        }

        for ($i = 0; $i < 124; $i++) {
            $categoryName = $faker->randomElement(array_keys($productsData));
            $productName = $faker->randomElement($productsData[$categoryName]);
            $randomUnit = $faker->randomElement(ProductUnit::cases());
            $productImage = $images[$productName] ?? null;

            $product = new Product();
            $product
                ->setName($productName)
                ->setPrice($faker->randomFloat(2, 1, 20))
                ->setUnit($randomUnit)
                ->setStock($faker->numberBetween(1, 100))
                ->setUser($this->getReference('user_' . rand(0, 4)))
                ->setCategorie($categories[$categoryName])
                ->setImage($productImage)
                ->setCreatedAt(new DateTimeImmutable())
                ->setUpdatedAt(new DateTimeImmutable());

            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
