<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class NewsController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(Environment $twig, NewsRepository $newsRepository): Response
    {
        return new Response($twig->render('news/index.html.twig', [
            'news' => $newsRepository->findAll(),
        ]));
    }

    #[Route('/news/{id}', name: 'news')]
    public function show(Environment $twig, News $news): Response
    {
        return new Response($twig->render('news/detail.html.twig', [
            'item' => $news,
        ]));
    }    
}
