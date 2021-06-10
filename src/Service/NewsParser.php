<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\Source;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NewsParser
{

    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private NewsRepository $newsRepository;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager, NewsRepository $newsRepository)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->newsRepository = $newsRepository;
    }

    public function parseSource(Source $source)
    {
        if ($content = $this->readUrl($source->getUrl())) {
            $news = $this->getNewsList($source, $content);
            foreach ($news as $item) {
                $this->getNewsDetails($item);
            }
        }
    }

    public function parseNews(News $news)
    {
        $this->getNewsDetails($news);
    }

    private function readUrl(string $url): string
    {
        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() === 200) {
                return $response->getContent();
            }
        } catch (ExceptionInterface $e) {
        }
        return '';
    }

    private function getNewsList(Source $source, string $content): ?array
    {
        $crawler = new Crawler($content);

        return $crawler->filter($source->getSelector())->each(function (Crawler $node, $i) use ($source) {
            $url = $node->attr('href');

            $news = $this->newsRepository->findOneByUrl($url);
            if (!$news) {
                $news = new News();
                $news->setUrl($url);
                $news->setDateAt(new \DateTime());
            }
            $news->setTitle($node->text());
            $news->setSource($source);
            return $news;
        });
    }

    private function getNewsDetails(News $news)
    {
        if ($content = $this->readUrl($news->getUrl())) {

            if ($news->getSource()->getName() == 'RBC') {
                $detailParser = new NewsDetailParserRBC($news, $content);
            } else {
                $detailParser = new NewsDetailParser($news, $content);
            }

            $detailParser->getNewsDetails();

            $this->entityManager->persist($news);
            $this->entityManager->flush();
        }
    }
}