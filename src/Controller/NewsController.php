<?php

namespace App\Controller;

use App\Entity\News;
use App\Service\NewsParser;
use App\Repository\NewsRepository;
use App\Repository\SourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class NewsController extends AbstractController
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
    
    #[Route('/', name: 'homepage')]
    public function index(NewsRepository $newsRepository, SourceRepository $sourceRepository, NewsParser $newsParser): Response
    {

        $sources = $sourceRepository->findAll();
        foreach ($sources as $source) {
            $newsParser->parseSource($source);
        }


        return new Response($this->twig->render('news/index.html.twig', [
            'news' => $newsRepository->findAll(),
        ]));
    }

    #[Route('/news/{id}', name: 'news')]
    public function show(News $news): Response
    {
        return new Response($this->twig->render('news/detail.html.twig', [
            'item' => $news,
        ]));
    }    
}
