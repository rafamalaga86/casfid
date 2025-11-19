<?php

declare(strict_types=1);

namespace App\Scraper;

/**
 * Scrapes headlines from El Mundo newspaper.
 */
class ElMundoScraper extends AbstractScraper
{
    private const SCRAPE_URL = 'https://www.elmundo.es/';
    private const HEADLINE_SELECTOR = 'article:not(.ue-c-cover-content--xs-from-mobile) .ue-c-cover-content__headline';
    private const IDENTIFIER = 'elmundo';

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
