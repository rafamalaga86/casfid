<?php

declare(strict_types=1);

namespace App\Service;

use App\Scraper\Exception\ScrapingException;
use App\Scraper\ScraperInterface;
use Psr\Log\LoggerInterface;

/**
 * Service to orchestrate the scraping process from multiple sources.
 */
class ScrapingService
{
    /**
     * @param iterable<ScraperInterface> $scrapers      A collection of all available scrapers.
     * @param LoggerInterface            $scraperLogger The logger for scraping-related messages.
     */
    public function __construct(
        private readonly iterable $scrapers,
        private readonly LoggerInterface $scraperLogger
    ) {
    }

    /**
     * Executes all registered scrapers and returns a report of the results and failures.
     *
     * @return array{
     *     results: array<string, array{
     *         headlines: array<int, array{title: string, url: string}>,
     *         count: int
     *     }>,
     *     errors: array<string, string>
     * } An associative array containing 'results' and 'errors'.
     */
    public function scrape(): array
    {
        $results = [];
        $errors = [];

        foreach ($this->scrapers as $scraper) {
            $scraperClass = get_class($scraper);

            try {
                $headlines = $scraper->scrape();

                if (empty($headlines)) {
                    $this->scraperLogger->warning('No headlines found.', [
                        'scraper' => $scraperClass,
                    ]);
                    // Store a warning, but not as a hard error
                    $errors[$scraperClass] = 'No headlines were found. The website structure might have changed or the selector is wrong.';
                    continue;
                }

                $count = count($headlines);
                $results[$scraperClass] = [
                    'headlines' => $headlines,
                    'count' => $count,
                ];

                $this->scraperLogger->info(sprintf('Successfully scraped %d headlines.', $count), [
                    'scraper' => $scraperClass,
                ]);

            } catch (ScrapingException $e) {
                $errorMessage = sprintf('An error occurred during scraping: %s', $e->getMessage());
                $errors[$scraperClass] = $errorMessage;
                $this->scraperLogger->error('Scraping failed.', [
                    'scraper' => $scraperClass,
                    'exception' => $e,
                ]);
            }
        }

        return [
            'results' => $results,
            'errors' => $errors,
        ];
    }
}
