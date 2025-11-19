<?php

declare(strict_types=1);

namespace App\Scraper;

/**
 * Defines the contract for all newspaper scrapers.
 */
interface ScraperInterface
{
    /**
     * Scrapes the main headlines from the newspaper's front page.
     *
     * @return array<int, array{title: string, url: string}>
     */
    public function scrape(): array;

    /**
     * Indicates if this scraper applies to a specific source (e.g., by URL).
     * This can be used by a factory or strategy pattern later.
     */
    public function supports(string $source): bool;
}
