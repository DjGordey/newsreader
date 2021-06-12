<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class NewsParser
{
    const NEWS_LIST_READ_LIMIT = 15;

    private SourceReader $sourceReader;
    private EntityManagerInterface $entityManager;

    public function __construct(SourceReader $sourceReader, EntityManagerInterface $entityManager)
    {
        $this->sourceReader = $sourceReader;
        $this->entityManager = $entityManager;
    }

    public function parseSource(Source $source): array
    {
        if ($content = $this->sourceReader->readUrl($source->getUrl())) {
            $news = $this->getNewsList($source, $content);
            foreach ($news as $item) {
                $this->getNewsDetails($item);
            }
            return $news;
        }
        return [];
    }

    public function parseNews(News $news)
    {
        $this->getNewsDetails($news);
    }

    private function getNewsList(Source $source, string $content): ?array
    {
        $crawler = new Crawler($content);

        $newsRepository = $this->entityManager->getRepository(News::class);

        return $crawler->filter($source->getSelector())->slice(0, self::NEWS_LIST_READ_LIMIT)->each(function (Crawler $node) use ($source, $newsRepository) {
            $url = $node->attr('href');

            $news = $newsRepository->findOneByUrl($url);
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