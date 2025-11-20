<?php

declare(strict_types=1);

namespace App\Scraper;

/**
 * Scrapes headlines from El Pais newspaper.
 */
class ElPaisScraper extends AbstractScraper
{
    /**
     * The URL of the El Pais homepage.
     */
    private const SCRAPE_URL = 'https://elpais.com';

    /**
     * The CSS selector for the headlines.
     */
    private const HEADLINE_SELECTOR = 'article h2 a';

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
    protected function getHeadlineSelector(): string
    {
        return self::HEADLINE_SELECTOR;
    }
}
