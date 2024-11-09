<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Address;
use Symfony\Component\Uid\Uuid;

class RegistrationController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles(['ROLE_USER']);

            // Générer un token de confirmation unique
            $user->setConfirmationToken(Uuid::v4()->toRfc4122());
            $this->logger->info('Confirmation token: ' . $user->getConfirmationToken());


            // Enregistrer l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi d'un email de confirmation
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@freshgarden.com', 'Fresh Garden'))
                ->to($user->getEmail())
                ->subject('Bienvenue sur Fresh Garden !')
                ->htmlTemplate('emails/registration_confirmation.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $mailer->send($email);

            // Rediriger vers la page de connexion
            $this->addFlash('success', 'Votre inscription est réussie. Un email de confirmation vous a été envoyé.');
            return $this->redirectToRoute('login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/confirm-email/{token}', name: 'email_confirmation')]
    public function confirmEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        // Rechercher l'utilisateur avec le token fourni
        $user = $entityManager->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Le token de confirmation est invalide ou a déjà été utilisé.');
            return $this->redirectToRoute('home');
        }

        // Confirmer l'email de l'utilisateur
        $user->setConfirmationToken(null);
        $user->setIsVerified(true); // Assurez-vous d'avoir un champ isVerified dans votre entité User
        $entityManager->flush();

        $this->addFlash('success', 'Votre adresse email a été confirmée avec succès.');
        return $this->redirectToRoute('login');
    }
}
