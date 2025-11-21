<?php

declare(strict_types=1);

namespace App\Scraper;

use App\Scraper\Enum\ScraperIdentifierEnum;

/**
 * Scrapes articles from El Mundo newspaper.
 */
class ElMundoScraper extends AbstractScraper
{
    /**
     * The URL of the El Mundo homepage.
     */
    private const SCRAPE_URL = 'https://www.elmundo.es/';
    /**
     * The CSS selector for the article links on the front page.
     */
    private const ARTICLE_LINK_SELECTOR = 'article:not(.ue-c-cover-content--xs-from-mobile) .ue-c-cover-content__headline';

    protected function getIdentifier(): ScraperIdentifierEnum
    {
        return ScraperIdentifierEnum::ElMundo;
    }

    protected function getScrapeUrl(): string
    {
        return self::SCRAPE_URL;
    }

    protected function getArticleLinkSelector(): string
    {
        return self::ARTICLE_LINK_SELECTOR;
    }

    protected function getArticleTitleSelector(): string
    {
        // This is a guess, might need adjustment.
        return 'h1.ue-c-article__headline';
    }

    protected function getArticleBodySelector(): string
    {
        // This is a guess, might need adjustment.
        return '.ue-c-article__body p';
    }
}
