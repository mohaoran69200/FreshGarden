<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
        // Initialisation du logger
        $this->logger = $logger;
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        // Crée un nouvel utilisateur et le formulaire d'inscription
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

            // Génère un token de confirmation unique
            $user->setConfirmationToken(Uuid::v4()->toRfc4122());
            $user->setResetTokenCreatedAt(new DateTimeImmutable());
            $this->logger->info('Confirmation token: ' . $user->getConfirmationToken());

            // Enregistre l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoie un email de confirmation
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@freshgarden.com', 'Fresh Garden'))
                ->to($user->getEmail())
                ->subject('Bienvenue sur Fresh Garden !')
                ->htmlTemplate('emails/registration_confirmation.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $mailer->send($email);

            // Redirige vers la page de connexion
            $this->addFlash('success', 'Votre inscription est réussie. Un email de confirmation vous a été envoyé.');
            return $this->redirectToRoute('login');
        }

        // Rend le formulaire d'inscription
        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/confirm-email/{token}', name: 'email_confirmation')]
    public function confirmEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        // Recherche l'utilisateur avec le token et vérifie sa validité
        $user = $entityManager->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token,
            'isVerified' => false
        ]);

        if (!$user) {
            $this->addFlash('danger', 'Le token de confirmation est invalide ou a déjà été utilisé.');
            return $this->redirectToRoute('home');
        }

        // Vérifie si le délai de 48 heures est dépassé
        $now = new DateTime();
        $resetTokenCreatedAt = $user->getResetTokenCreatedAt();

        if ($resetTokenCreatedAt && $now > (clone $resetTokenCreatedAt)->modify('+48 hours')) {
            $this->addFlash('danger', 'Le lien de confirmation a expiré. Veuillez demander un nouveau lien.');
            return $this->redirectToRoute('home');
        }

        // Confirme l'email de l'utilisateur
        $user->setConfirmationToken(null);
        $user->setResetTokenCreatedAt(new \DateTimeImmutable());
        $user->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Votre adresse email a été confirmée avec succès.');
        return $this->redirectToRoute('login');
    }
}
