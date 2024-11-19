<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        // Récupérer la page courante
        $page = $request->query->getInt('page', 1);

        // Récupérer les utilisateurs paginés
        $users = $paginator->paginate(
            $userRepository->createQueryBuilder('u')->orderBy('u.id', 'ASC'),
            $page,
            20 // Nombre d'utilisateurs par page
        );

        // Récupérer les utilisateurs bannis (isBanned = true)
        $bannedUsers = $userRepository->findBy(['isBanned' => true]);

        return $this->render('admin/admin_user/index.html.twig', [
            'users' => $users,
            'bannedUsers' => $bannedUsers,
        ]);
    }

// Pour bannir un utilisateur
    #[Route('/admin/user/{id}/ban', name: 'app_admin_user_ban')]
    public function ban(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsBanned(true);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_user_index');
    }

// Pour débannir un utilisateur
    #[Route('/admin/user/{id}/unban', name: 'app_admin_user_unban')]
    public function unban(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsBanned(false);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_user_index');
    }
}
