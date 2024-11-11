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
            'Lyon' => ['69001','69002','69003','69004','69005','69006','69007','69008','69009'],
            'Vénissieux' => '69200',
            'Saint-Priest' => '69800',
            'Decines' => '69150',
            'Ecully' => '69130',
            'Corbas' => '69960',
            'Bron' => '69500',
            'Villeurbanne' => '69100',
            'Meyzieu' => '69330',
            'Mions' => '69780',
            'Feyzin' => '69320',
            'Francheville' => '69340'
        ];

        // Récupère toutes les valeurs possibles de l'énumération UserGender
        $genders = UserGender::cases(); // Retourne un tableau d'instances de UserGender

        // Tableau pour garder trace des noms d'utilisateur existants
        $existingUsernames = [];


        // Création des utilisateurs avec des données aléatoires
        for ($i = 0; $i < 46; $i++) {
            // Sélectionne une ville aléatoire et obtient son code postal
            $city = $faker->randomElement(array_keys($citiesData));
            // Vérifie si la ville a plusieurs codes postaux
            if (is_array($citiesData[$city])) {
                // Sélectionne un code postal aléatoire
                $postalCode = $faker->randomElement($citiesData[$city]);
            } else {
                // Sinon, utilise le code postal unique
                $postalCode = $citiesData[$city];
            }

            // Sélectionne un genre aléatoire parmi ceux définis dans l'énumération
            $randomGender = $faker->randomElement($genders);

            // Génération des prénoms et noms de famille
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();

            // Génération du nom d'utilisateur
            $username = strtolower($firstName) . strtoupper(substr($lastName, 0, 1)); // Exemple: jeanD

            // Assure-toi que le nom d'utilisateur est unique
            $counter = 1;
            while (in_array($username, $existingUsernames)) {
                $username = strtolower($firstName) . strtoupper(substr($lastName, 0, 1)) . $counter;
                $counter++;
            }
            $existingUsernames[] = $username;

            // Création d'un profil utilisateur
            $userProfile = new UserProfile();
            $userProfile
                ->setUserName($username)
                ->setFirstName($firstName) // Utiliser le prénom généré
                ->setLastName($lastName) // Utiliser le nom de famille généré
                ->setDateBirth(new DateTimeImmutable($faker->date())) // Date de naissance aléatoire
                ->setAddress($faker->streetAddress()) // Adresse de rue aléatoire
                ->setCity($city) // Ville aléatoire
                ->setPhoneNumber($faker->numerify('0#########')) // Numéro de téléphone aléatoire de 10 chiffres
                ->setPostalCode($postalCode) // Code postal correspondant à la ville
                ->setGender($randomGender); // Genre aléatoire selon l'énumération

            // Création d'un utilisateur
            $user = new User();
            $user
                ->setEmail($username . '@mail.com') // Adresse email unique pour chaque utilisateur
                ->setPassword($this->userPasswordHasherInterface->hashPassword(
                    $user,
                    'password' // Mot de passe par défaut
                ))
                ->setRoles(['ROLE_USER']) // Rôle utilisateur
                ->setIsVerified(true)
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
            ->setPhoneNumber('0613174668')
            ->setPostalCode('69200') // Code postal spécifique pour l'admin
            ->setGender(UserGender::Monsieur); // Genre spécifique pour l'admin


        // Création de l'utilisateur admin
        $user = new User();
        $user
            ->setEmail('admin@mail.com') // Adresse email spécifique pour l'admin
            ->setPassword($this->userPasswordHasherInterface->hashPassword(
                $user,
                'password' // Mot de passe par défaut pour l'admin
            ))
            ->setRoles(['ROLE_ADMIN']) // Rôle admin
            ->setIsVerified(true)
            ->setUserProfile($userProfile); // Liaison avec le profil utilisateur

        // Persiste l'utilisateur admin dans la base de données
        $manager->persist($user);

        // Enregistre toutes les entités persistées dans la base de données
        $manager->flush();
    }
}
