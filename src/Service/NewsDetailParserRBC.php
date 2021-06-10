<?php

namespace App\Service;

class NewsDetailParserRBC extends NewsDetailParser
{
    protected static array $titleSelectors = [
        'h1.article__header__title-in',
        'h2.article__title'
    ];

    protected static array $dateSelectors = [
        'div.article__date',
        'span.article__header__date',
    ];

    protected static array $imageSelectors = [
        'img.article__main-image__image',
        'img.js-rbcslider-image',
    ];

    protected static string $textSelector =
        'div.article__text';

    protected static array $removeBlocks = [
        'div.article__ticker__link',
        'div.news-bar_article',
        'div.article__inline-item',
        'div.article__special_container',
        'div.article__main-image',
        'div.banner',
        'div.article__tags',
        'div.pro-anons',
    ];
}