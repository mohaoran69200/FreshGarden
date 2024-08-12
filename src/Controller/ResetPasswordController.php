<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

// Route de base pour toutes les actions de réinitialisation de mot de passe
#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait; // Utilisation du trait pour les fonctionnalités de réinitialisation

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper, // Helper pour la réinitialisation du mot de passe
        private EntityManagerInterface $entityManager // Gestionnaire d'entités Doctrine
    ) {
    }

    /**
     * Affiche et traite le formulaire de demande de réinitialisation de mot de passe.
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request,
                            MailerInterface $mailer,
                            TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class); // Création du formulaire de demande de réinitialisation
        $form->handleRequest($request); // Traite la soumission du formulaire

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est soumis et valide, envoie l'email de réinitialisation
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(), // Récupère l'email soumis
                $mailer,
                $translator
            );
        }

        // Affiche le formulaire de demande de réinitialisation
        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form,
        ]);
    }

    /**
     * Page de confirmation après qu'un utilisateur a demandé une réinitialisation de mot de passe.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Génère un faux token si l'utilisateur n'existe pas ou si la page est accédée directement
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        // Affiche la page de confirmation avec le token
        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Valide et traite l'URL de réinitialisation que l'utilisateur a cliqué dans son email.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request,
                          UserPasswordHasherInterface $passwordHasher,
                          TranslatorInterface $translator,
                          ?string $token = null): Response
    {
        if ($token) {
            // Stocke le token en session et le supprime de l'URL pour éviter les fuites
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession(); // Récupère le token de la session

        if (null === $token) {
            // Si aucun token n'est trouvé, lève une exception
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            /** @var User $user */
            // Valide le token et récupère l'utilisateur associé
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            // Si une exception se produit, ajoute un message flash d'erreur et redirige vers la page de demande
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Le token est valide, permet à l'utilisateur de changer son mot de passe
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Supprime la demande de réinitialisation après utilisation du token
            $this->resetPasswordHelper->removeResetRequest($token);

            // Hache le mot de passe en clair et le définit
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($encodedPassword);
            $this->entityManager->flush(); // Enregistre les changements

            // Nettoie la session après la réinitialisation du mot de passe
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('app_home'); // Redirige vers la page d'accueil
        }

        // Affiche le formulaire de changement de mot de passe
        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData,
                                                      MailerInterface $mailer,
                                                      TranslatorInterface $translator): RedirectResponse
    {
        // Recherche l'utilisateur par email
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Ne révèle pas si un compte utilisateur a été trouvé ou non
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            // Génère un token de réinitialisation pour l'utilisateur
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // En cas d'exception, redirige vers la page de demande de réinitialisation
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));
            return $this->redirectToRoute('app_check_email');
        }

        // Crée et envoie l'email de réinitialisation
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@freshgarden.com', 'No Reply')) // Adresse de l'expéditeur
            ->to($user->getEmail()) // Adresse de destination
            ->subject('Your password reset request') // Sujet de l'email
            ->htmlTemplate('reset_password/email.html.twig') // Template de l'email
            ->context([
                'resetToken' => $resetToken, // Contexte incluant le token de réinitialisation
            ])
        ;
        $mailer->send($email); // Envoie l'email

        // Stocke le token en session pour récupération dans la route check-email
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email'); // Redirige vers la page de confirmation
    }
}
