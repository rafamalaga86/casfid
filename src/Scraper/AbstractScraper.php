<?php

declare(strict_types=1);

namespace App\Scraper;

use App\DTO\NewsArticleDTO;
use App\Scraper\Enum\ScraperIdentifierEnum;
use App\Scraper\Exception\ScrapingException;
use Psr\Log\LoggerInterface;
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
        protected readonly int $articleLimit,
        /**
         * The logger for scraping-related messages.
         */
        protected readonly LoggerInterface $scraperLogger,
    ) {
    }

    /**
     * Scrapes the newspaper using the URL and selector provided by the concrete class.
     *
     * @return NewsArticleDTO[] the list of scraped articles
     *
     * @throws ScrapingException if the scraping process fails
     */
    public function scrape(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->getScrapeUrl());
            $html = $response->getContent();
        } catch (TransportExceptionInterface $e) {
            throw new ScrapingException(sprintf('Failed to fetch front page from %s: %s', $this->getScrapeUrl(), $e->getMessage()), 0, $e);
        }

        $crawler = new Crawler($html);
        $baseUrl = $this->getBaseUrl();

        $articleLinkNodes = $crawler
            ->filter($this->getArticleLinkSelector())
            ->slice(0, $this->articleLimit);

        $results = [];

        foreach ($articleLinkNodes as $node) {
            $nodeCrawler = new Crawler($node);

            // If the node is not a link, try to find a parent link
            $linkNode = $nodeCrawler;
            if ('a' !== $linkNode->nodeName()) {
                $linkNode = $nodeCrawler->closest('a');
            }

            if (0 === $linkNode->count() || empty($linkNode->attr('href'))) {
                continue;
            }

            $url = $linkNode->attr('href');

            // Ensure the URL is absolute
            if (!str_starts_with($url, 'http')) {
                $url = $baseUrl.$url;
            }

            try {
                $articleResponse = $this->httpClient->request('GET', $url);
                $articleHtml = $articleResponse->getContent();
                $articleCrawler = new Crawler($articleHtml, $url);

                $title = $articleCrawler->filter($this->getArticleTitleSelector())->text('');
                $body = $articleCrawler->filter($this->getArticleBodySelector())
                    ->each(fn (Crawler $p) => $p->text(''));

                $results[] = new NewsArticleDTO(
                    title: trim($title),
                    url: $url,
                    body: trim(implode("\n\n", $body))
                );
            } catch (TransportExceptionInterface $e) {
                $this->scraperLogger->warning(sprintf('Failed to fetch article content from %s: %s', $url, $e->getMessage()), [
                    'url' => $url,
                    'scraper' => static::class,
                    'exception' => $e,
                ]);
                continue;
            }
        }

        return $results;
    }

    /**
     * A simple support check based on the domain name.
     */
    public function supports(string $source): bool
    {
        return str_contains(strtolower($source), $this->getIdentifier()->value);
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
     * A unique identifier for the newspaper.
     */
    abstract public function getIdentifier(): ScraperIdentifierEnum;

    /**
     * The full URL of the page to be scraped.
     */
    abstract protected function getScrapeUrl(): string;

    /**
     * The CSS selector to find the article link elements on the front page.
     */
    abstract protected function getArticleLinkSelector(): string;

    /**
     * The CSS selector for the main title on an article page.
     */
    abstract protected function getArticleTitleSelector(): string;

    /**
     * The CSS selector for the paragraphs that make up the article's body.
     */
    abstract protected function getArticleBodySelector(): string;
}
