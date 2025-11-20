<?php

declare(strict_types=1);

namespace App\Scraper;

use App\DTO\HeadlineDTO;
use App\Scraper\Exception\ScrapingException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Base class for scrapers to share common logic.
 */
abstract class AbstractScraper implements ScraperInterface
{
    public function __construct(
        /**
         * The HTTP client to make requests.
         */
        protected readonly HttpClientInterface $httpClient,
        /**
         * The maximum number of articles to scrape.
         */
        protected readonly int $articleLimit
    ) {
    }

    /**
     * Scrapes the newspaper using the URL and selector provided by the concrete class.
     *
     * @return HeadlineDTO[] The list of scraped headlines.
     * @throws ScrapingException If the scraping process fails.
     */
    public function scrape(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->getScrapeUrl());
            $html = $response->getContent();
        } catch (TransportExceptionInterface $e) {
            throw new ScrapingException(sprintf('Failed to fetch content from %s: %s', $this->getScrapeUrl(), $e->getMessage()), 0, $e);
        }

        $crawler = new Crawler($html);

        $headlines = [];
        $baseUrl = $this->getBaseUrl();

        $crawler
            ->filter($this->getHeadlineSelector())
            ->slice(0, $this->articleLimit)
            ->each(function (Crawler $node) use (&$headlines, $baseUrl) {
                $title = trim($node->text(''));

                // If the node is not a link, try to find a parent link
                $linkNode = $node;
                if ('a' !== $linkNode->nodeName()) {
                    $linkNode = $node->closest('a');
                }

                // If no link found, we can't get a URL.
                if (0 === $linkNode->count()) {
                    return;
                }

                $url = $linkNode->attr('href');

                if (empty($title) || empty($url)) {
                    return;
                }

                // Ensure the URL is absolute
                if (!str_starts_with($url, 'http')) {
                    $url = $baseUrl . $url;
                }

                $headlines[] = new HeadlineDTO(title: $title, url: $url);
            });

        return $headlines;
    }

    /**
     * A simple support check based on the domain name.
     */
    public function supports(string $source): bool
    {
        return str_contains(strtolower($source), strtolower($this->getIdentifier()));
    }

    /**
     * Returns the base URL for resolving relative links.
     */
    private function getBaseUrl(): string
    {
        $urlParts = parse_url($this->getScrapeUrl());
        return sprintf('%s://%s', $urlParts['scheme'], $urlParts['host']);
    }

    /**
     * A unique lowercase identifier for the newspaper (e.g., 'elpais').
     */
    abstract protected function getIdentifier(): string;

    /**
     * The full URL of the page to be scraped.
     */
    abstract protected function getScrapeUrl(): string;

    /**
     * The CSS selector to find the headline elements.
     */
    abstract protected function getHeadlineSelector(): string;
}