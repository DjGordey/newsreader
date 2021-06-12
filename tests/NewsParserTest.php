<?php

namespace App\Tests;

use App\Entity\News;
use App\Entity\Source;
use App\Repository\NewsRepository;
use App\Service\NewsParser;
use App\Service\SourceReader;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class NewsParserTest extends TestCase
{
    public function testList(): void
    {
        $client = new MockHttpClient([new MockResponse(
            file_get_contents('tests/data/index.html')
        )]);

        $sourceReader = new SourceReader($client);

        $source = new Source();
        $source->setName('RBC');
        $source->setUrl('https://www.rbc.fake');
        $source->setSelector('div.js-news-feed-list > a.js-news-feed-item');

        $newsRepository = $this
            ->getMockBuilder(NewsRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($newsRepository));

        $newsParser = new NewsParser($sourceReader, $entityManager);
        $news = $newsParser->parseSource($source);


        $this->assertEquals(14, count($news));
        $this->assertIsInt(strpos($news[0]->getTitle(), 'В Подмосковье задержали подозреваемую в похищении ребенка'));

    }

    public function testDetails(): void
    {
        $client = new MockHttpClient([new MockResponse(
            file_get_contents('tests/data/details.html')
        )]);

        $sourceReader = new SourceReader($client);

        $source = new Source();
        $source->setName('RBC');
        $news = new News();
        $news->setSource($source);
        $news->setUrl('https://www.rbc.fake/detail');

        $entityManager = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $newsParser = new NewsParser($sourceReader, $entityManager);
        $newsParser->parseNews($news);

        $this->assertEquals($news->getTitle(), 'CNN(test) узнал о надежде Байдена на возвращение послов после встречи с Путиным');
        $this->assertEquals($news->getDateAt()->format('c'), '2021-06-10T22:05:05+03:00');
        $this->assertEquals($news->getImage(), 'https://s0.rbk.ru/v6_top_pics/resized/1200xH/media/img/4/16/756233525780164.jpg');
        $this->assertEquals(0, strpos($news->getPreview(), 'Администрация США надеется, что президенты'));
        $this->assertFalse(strpos($news->getPreview(), 'Bla bla bla'));
        $this->assertIsInt(strpos($news->getText(), 'Администрация США надеется, что президенты'));
    }
}
