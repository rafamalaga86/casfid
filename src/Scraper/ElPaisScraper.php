<?php

declare(strict_types=1);

namespace App\Scraper;

/**
 * Scrapes articles from El Pais newspaper.
 */
class ElPaisScraper extends AbstractScraper
{
    /**
     * The URL of the El Pais homepage.
     */
    private const SCRAPE_URL = 'https://elpais.com';

    /**
     * The CSS selector for the article links on the front page.
     */
    private const ARTICLE_LINK_SELECTOR = 'article h2 a';

    /**
     * The unique identifier for the scraper.
     */
    private const IDENTIFIER = 'elpais';

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
        return 'h1';
    }

    /**
     * {@inheritdoc}
     */
    protected function getArticleBodySelector(): string
    {
        // This is a guess, might need adjustment.
        return 'div.a_c p';
    }
}
