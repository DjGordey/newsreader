<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\Source;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
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

    private function readUrl(string $url): string
    {
        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() === 200) {
                return $response->getContent();
            }
        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
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
            }
            $news->setTitle($node->text());
            $news->setSource($source);
            return $news;
        });
    }

    private function getNewsDetails(News $news)
    {
        if ($content = $this->readUrl($news->getUrl())) {
            $crawler = new Crawler($content);

            $crawler->filter('div.article__content')->each(function (Crawler $article) use ($news) {

                $article->filter('div.article__text')->each(function (Crawler $text) use ($news) {
                    $text->filter('div.news-bar_article')->each(function (Crawler $bar) {
                        foreach ($bar as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });

                    $news->setText($text->eq(0)->text());
                });

                $article->filter('span.article__header__date')->each(function (Crawler $date) use ($news) {
                    $news->setDateAt(new \DateTime($date->attr('content')));
                });

                $article->filter('img.article__main-image__image')->each(function (Crawler $image) use ($news) {
                    $news->setImage($image->attr('src'));
                });

                $this->entityManager->persist($news);
                $this->entityManager->flush();
            });
        }
    }
}