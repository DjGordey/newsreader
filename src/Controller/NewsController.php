<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use App\Repository\SourceRepository;
use App\Service\NewsParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class NewsController extends AbstractController
{
    private Environment $twig;
    private NewsParser $newsParser;

    public function __construct(Environment $twig, NewsParser $newsParser)
    {
        $this->twig = $twig;
        $this->newsParser = $newsParser;
    }

    #[Route('/', name: 'homepage')]
    public function index(NewsRepository $newsRepository, SourceRepository $sourceRepository, Request $request): Response
    {

        if ($request->query->get('reload')) {
            $sources = $sourceRepository->findAll();
            foreach ($sources as $source) {
                $this->newsParser->parseSource($source);
            }
            return $this->redirectToRoute('homepage');
        }

        return new Response($this->twig->render('news/index.html.twig', [
            'news' => $newsRepository->findAll(),
        ]));
    }

    #[Route('/news/{id}', name: 'news')]
    public function show(News $news, Request $request): Response
    {

        if ($request->query->get('reload')) {
            $this->newsParser->parseNews($news);
            return $this->redirectToRoute('news', ['id' => $news->getId()]);
        }

        return new Response($this->twig->render('news/detail.html.twig', [
            'item' => $news,
        ]));
    }
}
