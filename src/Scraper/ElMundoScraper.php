<?php

declare(strict_types=1);

namespace App\Scraper;

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
    /**
     * The unique identifier for the scraper.
     */
    private const IDENTIFIER = 'elmundo';

    /**
     * {@inheritdoc}
     */
    protected function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScrapeUrl(): string
    {
        return self::SCRAPE_URL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getArticleLinkSelector(): string
    {
        return self::ARTICLE_LINK_SELECTOR;
    }

    /**
     * {@inheritdoc}
     */
    protected function getArticleTitleSelector(): string
    {
        // This is a guess, might need adjustment.
        return 'h1.ue-c-article__headline';
    }

    /**
     * {@inheritdoc}
     */
    protected function getArticleBodySelector(): string
    {
        // This is a guess, might need adjustment.
        return '.ue-c-article__body p';
    }
}
