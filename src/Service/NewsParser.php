<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\Source;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class NewsParser
{
    const NEWS_LIST_READ_LIMIT = 15;

    private SourceReader $sourceReader;
    private EntityManagerInterface $entityManager;
    private NewsRepository $newsRepository;

    public function __construct(SourceReader $sourceReader, EntityManagerInterface $entityManager, NewsRepository $newsRepository)
    {
        $this->sourceReader = $sourceReader;
        $this->entityManager = $entityManager;
        $this->newsRepository = $newsRepository;
    }

    public function parseSource(Source $source)
    {
        if ($content = $this->sourceReader->readUrl($source->getUrl())) {
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

    private function getNewsList(Source $source, string $content): ?array
    {
        $crawler = new Crawler($content);

        return $crawler->filter($source->getSelector())->slice(0, self::NEWS_LIST_READ_LIMIT)->each(function (Crawler $node) use ($source) {
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
        if ($news->getSource()->getName() == 'RBC') {
            $detailParser = new NewsDetailParserRBC($this->sourceReader, $news);
        } else {
            $detailParser = new NewsDetailParser($this->sourceReader, $news);
        }

        $detailParser->getNewsDetails();

        $this->entityManager->persist($news);
        $this->entityManager->flush();
    }
}