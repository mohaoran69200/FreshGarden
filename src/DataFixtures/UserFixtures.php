<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserGender;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\File;

class UserFixtures extends Fixture
{
    // Constructeur pour injecter le service de hashage de mot de passe
    public function __construct(public UserPasswordHasherInterface $userPasswordHasherInterface)
    {}

    // Méthode principale de chargement des fixtures
    public function load(ObjectManager $manager): void
    {
        // Crée un utilisateur administrateur
        $this->createAdminUser($manager);

        // Crée une instance de Faker pour générer des données de test en français
        $faker = Factory::create('fr_FR');

        // Liste des villes avec leurs codes postaux pour générer des adresses
        $citiesData = [
            'Lyon' => '69008',
            'Venissieux' => '69200',
            'Saint-Priest' => '69800',
            'Decines' => '69150',
            'Ecully' => '69130',
        ];

        // Répertoires pour la gestion des fichiers d'images
        $sourceDir = dirname(__DIR__, 2) . '/assets/images/fixtures'; // Répertoire source des images
        $destinationDir = dirname(__DIR__, 2) . '/public/uploads/user_profile'; // Répertoire de destination des images

        // Création d'une instance de Filesystem pour manipuler les fichiers
        $filesystem = new Filesystem();

        // Vérifie si le répertoire source existe
        if (!$filesystem->exists($sourceDir)) {
            throw new \Exception("Le dossier source n'existe pas : " . $sourceDir);
        }

        // Vérifie si le répertoire de destination existe, sinon le crée
        if (!$filesystem->exists($destinationDir)) {
            $filesystem->mkdir($destinationDir, 0755);
        }

        // Copie les fichiers du répertoire source vers le répertoire de destination
        try {
            $files = scandir($sourceDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    for ($a = 0; $a < 5; $a++) {
                        $sourceFilePath = $sourceDir . '/' . $file;
                        $destinationFilePath = $destinationDir . '/' . $a . '-' . $file;

                        if (is_file($sourceFilePath)) {
                            $filesystem->copy($sourceFilePath, $destinationFilePath, true);
                        }
                    }
                }
            }
        } catch (IOExceptionInterface $exception) {
            echo "Une erreur est survenue lors de la copie des fichiers : " . $exception->getMessage();
        }

        // Récupère toutes les valeurs possibles de l'énumération UserGender
        $genders = UserGender::cases(); // Retourne un tableau d'instances de UserGender

        // Création des utilisateurs avec des données aléatoires
        for ($i = 0; $i < 5; $i++) {
            // Sélectionne une ville aléatoire et obtient son code postal
            $city = $faker->randomElement(array_keys($citiesData));
            $postalCode = $citiesData[$city];

            // Sélectionne un genre aléatoire parmi ceux définis dans l'énumération
            $randomGender = $faker->randomElement($genders);

            // Création d'un profil utilisateur
            $userProfile = new UserProfile();
            $userProfile
                ->setFirstName($faker->firstName()) // Prénom aléatoire
                ->setLastName($faker->lastName()) // Nom de famille aléatoire
                ->setDateBirth(new DateTimeImmutable($faker->date())) // Date de naissance aléatoire
                ->setAddress($faker->streetAddress()) // Adresse de rue aléatoire
                ->setCity($city) // Ville aléatoire
                ->setPhoneNumber('0696874521') // Numéro de téléphone aléatoire
                ->setPostalCode($postalCode) // Code postal correspondant à la ville
                ->setGender($randomGender) // Genre aléatoire selon l'énumération
                ->setImageName($i . '-profil.jpg'); // Nom de fichier d'image pour le profil

            // Création d'un utilisateur
            $user = new User();
            $user
                ->setEmail('user' . $i . '@mail.com') // Adresse email unique pour chaque utilisateur
                ->setPassword($this->userPasswordHasherInterface->hashPassword(
                    $user,
                    'password' // Mot de passe par défaut
                ))
                ->setRoles(['ROLE_USER']) // Rôle utilisateur
                ->setUserProfile($userProfile); // Liaison avec le profil utilisateur

            // Ajoute une référence à cet utilisateur pour d'autres fixtures potentielles
            $this->addReference('user_' . $i, $user);

            // Persiste l'utilisateur dans la base de données
            $manager->persist($user);
        }

        // Enregistre toutes les entités persistées dans la base de données
        $manager->flush();
    }

    // Méthode pour créer un utilisateur administrateur avec des données spécifiques
    public function createAdminUser(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création du profil pour l'utilisateur admin
        $userProfile = new UserProfile();
        $userProfile
            ->setFirstName($faker->firstName())
            ->setLastName($faker->lastName())
            ->setDateBirth(new DateTimeImmutable($faker->date()))
            ->setAddress($faker->streetAddress())
            ->setCity('Vénissieux') // Ville spécifique pour l'admin
            ->setPhoneNumber('0696874521')
            ->setPostalCode('69200') // Code postal spécifique pour l'admin
            ->setGender(UserGender::Monsieur) // Genre spécifique pour l'admin
            ->setImageName('admin-profil.jpg'); // Nom de fichier d'image pour le profil admin

        // Création de l'utilisateur admin
        $user = new User();
        $user
            ->setEmail('admin@mail.com') // Adresse email spécifique pour l'admin
            ->setPassword($this->userPasswordHasherInterface->hashPassword(
                $user,
                'password' // Mot de passe par défaut pour l'admin
            ))
            ->setRoles(['ROLE_ADMIN']) // Rôle admin
            ->setUserProfile($userProfile); // Liaison avec le profil utilisateur

        // Persiste l'utilisateur admin dans la base de données
        $manager->persist($user);

        // Enregistre toutes les entités persistées dans la base de données
        $manager->flush();
    }
}
