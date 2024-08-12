<?php

namespace App\Controller;

use App\DTO\SearchDto;
use App\Form\SearchType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/search', name: 'app_')]
class SearchController extends AbstractController
{
    #[Route('', name: 'search')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $search = new SearchDto();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             $results = $productRepository->search($search);
        }


        return $this->render('search/index.html.twig', [
            'form' => $form,
            'results' => $results ?? []
        ]);
    }
}
