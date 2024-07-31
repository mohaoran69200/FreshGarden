<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserGender;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(public UserPasswordHasherInterface $userPasswordHasherInterface)
    {}

    public function load(ObjectManager $manager): void
    {
        $this->createAdminUser($manager);

        $faker = Factory::create();
        $sourceDir = dirname(__DIR__, 2) . '/assets/images/fixtures';
        $destinationDir = dirname(__DIR__, 2) . '/public/uploads/user_profile';

        $filesystem = new Filesystem();
            if (!$filesystem->exists($sourceDir)) {
                throw new \Exception("Le dossier source n'existe pas : " . $sourceDir);
            }
            if (!$filesystem->exists($destinationDir)) {
                $filesystem->mkdir($destinationDir, 0755);
            }
            try {
                $files = scandir($sourceDir);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        for ($a=0; $a < 5; $a++) {
                            $sourceFilePath = $sourceDir . '/' . $file;
                            $destinationFilePath = $destinationDir . '/' . $a . '-'. $file;

                            if (is_file($sourceFilePath)) {
                                $filesystem->copy($sourceFilePath, $destinationFilePath, true);
                            }
                        }
                    }
                }
            } catch (IOExceptionInterface $exception) {
                echo "Une erreur est survenue lors de la copie des fichiers : " . $exception->getMessage();
            }


        for ($i=0; $i < 5; $i++) {
            $userProfile = new UserProfile();
            $userProfile
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setDateBirth(new DateTimeImmutable($faker->date()))
                ->setAddress($faker->address())
                ->setCity($faker->city())
                ->setPhoneNumber('0600000000')
                ->setPostalCode($faker->postcode())
                ->setGender(UserGender::Man)
                ->setImageName($i. '-profil.jpg')
            ;

            $user = new User();
            $user
                ->setEmail('user'. $i . '@mail.com')
                ->setPassword($this->userPasswordHasherInterface->hashPassword(
                    $user,
                    'password'
                ))
                ->setRoles(['ROLE_USER'])
                ->setUserProfile($userProfile)
                ;

            
            $manager->persist($user);
        }

        $manager->flush();
    }
    
    public function createAdminUser(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $userProfile = new UserProfile();
            $user = new User();
            $user
                ->setEmail('admin@mail.com')
                ->setPassword($this->userPasswordHasherInterface->hashPassword(
                    $user,
                    'password'
                ))
                ->setRoles(['ROLE_ADMIN'])
                ;

            
            $manager->persist($user);
    }
    // public function getDependencies(): array
    // {
    //     return [

    //     ];
    // }
}
