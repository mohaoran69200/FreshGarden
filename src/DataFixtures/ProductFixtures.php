<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 15; $i++) {
            $product = new Product();
            $product
            ->setName('Product ' . $i)
            ->setPrice(price: 10554848)
            ->setUnit()
            ->setStock()
            ->setImageName()
;
        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
