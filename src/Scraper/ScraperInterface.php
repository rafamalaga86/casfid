<?php

declare(strict_types=1);

namespace App\Scraper;

use App\DTO\NewsArticleDTO;

/**
 * Defines the contract for all newspaper scrapers.
 */
interface ScraperInterface
{
    /**
     * Scrapes news articles from the newspaper's front page.
     *
     * @return NewsArticleDTO[]
     */
    public function scrape(): array;

    /**
     * Indicates if this scraper applies to a specific source (e.g., by URL).
     * This can be used by a factory or strategy pattern later.
     */
    public function supports(string $source): bool;
}
