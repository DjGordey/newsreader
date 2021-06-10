<?php

namespace App\Service;

use App\Entity\News;
use Symfony\Component\DomCrawler\Crawler;

class NewsDetailParser
{

    protected static array $titleSelectors = [];

    protected static array $dateSelectors = [];

    protected static array $imageSelectors = [];

    protected static string $textSelector = '';

    protected static array $removeBlocks = [];

    private SourceReader $sourceReader;
    private News $news;
    private Crawler $crawler;

    public function __construct(SourceReader $sourceReader, News $news)
    {
        $this->news = $news;
        $this->sourceReader = $sourceReader;
        $content = $this->sourceReader->readUrl($news->getUrl());
        $this->crawler = new Crawler($content);
    }

    public function getNewsDetails()
    {
        $this->getNewsDetailsTitle();
        $this->getNewsDetailsDate();
        $this->getNewsDetailsImage();
        $this->getNewsDetailsText();
    }

    private function getNewsDetailsTitle()
    {
        foreach (static::$titleSelectors as $titleSelector) {
            $this->crawler->filter($titleSelector)->each(function (Crawler $title) {
                $this->news->setTitle($title->text());
            });
        }
    }

    private function getNewsDetailsDate()
    {
        foreach (static::$dateSelectors as $dateSelector) {
            $this->crawler->filter($dateSelector)->each(function (Crawler $date) {
                $this->news->setDateAt(new \DateTime($date->attr('content')));
            });
        }
    }

    private function getNewsDetailsImage()
    {
        foreach (static::$imageSelectors as $imageSelector) {
            if (!$this->news->getImage()) {
                $this->crawler->filter($imageSelector)->each(function (Crawler $image) {
                    $this->news->setImage($image->attr('src'));
                });
            }
        }
    }

    private function getNewsDetailsText()
    {
        $partText = [];
        $partHtml = [];
        $this->crawler->filter(static::$textSelector)->each(function (Crawler $text) use (&$partText, &$partHtml) {

            foreach (static::$removeBlocks as $removeBlock) {
                $text->filter($removeBlock)->each(function (Crawler $bar) {
                    foreach ($bar as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
            }

            $partHtml[] = trim($text->eq(0)->html());
            $partText[] = trim($text->eq(0)->text());
        });

        $this->news->setText(implode(PHP_EOL . PHP_EOL, $partHtml));
        $this->news->setPreview(mb_substr(implode(PHP_EOL, $partText), 0, 255));
    }
}