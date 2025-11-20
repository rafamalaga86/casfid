<?php

declare(strict_types=1);

namespace App\Scraper;

/**
 * Scrapes headlines from El Mundo newspaper.
 */
class ElMundoScraper extends AbstractScraper
{
    /**
     * The URL of the El Mundo homepage.
     */
    private const SCRAPE_URL = 'https://www.elmundo.es/';
    /**
     * The CSS selector for the headlines.
     */
    private const HEADLINE_SELECTOR = 'article:not(.ue-c-cover-content--xs-from-mobile) .ue-c-cover-content__headline';
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
    protected function getHeadlineSelector(): string
    {
        return self::HEADLINE_SELECTOR;
    }
}
