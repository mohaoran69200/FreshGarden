<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 15; $i++) {
            $product = new Product();
            $product
                ->setName($i)
                ->setPrice($i)
                ->setUnit()
                ->setStock(stock: int)
                ->setUser($this->getReference('user_'. rand(0, 4)))
;
            $manager->persist($product);
        }

        $manager->flush();
    }
    public function getDependencies(): array {
        return [UserFixtures::class];
    }
}
