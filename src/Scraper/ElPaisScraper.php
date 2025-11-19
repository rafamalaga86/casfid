<?php

declare(strict_types=1);

namespace App\Scraper;

/**
 * Scrapes headlines from El Pais newspaper.
 */
class ElPaisScraper extends AbstractScraper
{
    private const SCRAPE_URL = 'https://elpais.com';
    private const HEADLINE_SELECTOR = 'article h2 a';
    private const IDENTIFIER = 'elpais';

    protected function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    protected function getScrapeUrl(): string
    {
        return self::SCRAPE_URL;
    }

    protected function getHeadlineSelector(): string
    {
        return self::HEADLINE_SELECTOR;
    }
}
