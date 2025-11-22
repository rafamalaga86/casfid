<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\NewsArticleDTO;
use App\Repository\ArticleRepositoryInterface;
use App\Scraper\Exception\ScrapingException;
use App\Scraper\ScraperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service to orchestrate the scraping process from multiple sources.
 */
class ScrapingService
{
    /**
     * @param iterable<ScraperInterface> $scrapers      a collection of all available scrapers
     * @param LoggerInterface            $scraperLogger the logger for scraping-related messages
     */
    public function __construct(
        private readonly iterable $scrapers,
        private readonly LoggerInterface $scraperLogger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ArticleRepositoryInterface $articleRepository,
    ) {
    }

    /**
     * Executes all registered scrapers and returns a report of the results and failures.
     *
     * @return array{
     *     results: array<string, array{
     *         articles: NewsArticleDTO[],
     *         count: int
     *     }>,
     *     errors: array<string, string>
     * } An associative array containing 'results' and 'errors'
     */
    public function scrape(): array
    {
        $results = [];
        $errors = [];

        foreach ($this->scrapers as $scraper) {
            $scraperIdentifier = $scraper->getIdentifier()->value;

            try {
                $articles = $scraper->scrape();

                if (empty($articles)) {
                    $this->scraperLogger->warning('No articles found.', [
                        'scraper' => $scraperIdentifier,
                    ]);
                    // Store a warning, but not as a hard error
                    $errors[$scraperIdentifier] = 'No articles were found. The website structure might have changed or the selector is wrong.';
                    continue;
                }

                $this->saveArticles($articles, $scraper->getIdentifier());

                $count = count($articles);
                $results[$scraperIdentifier] = [
                    'articles' => $articles,
                    'count' => $count,
                ];

                $this->scraperLogger->info(sprintf('Successfully scraped and processed %d articles.', $count), [
                    'scraper' => $scraperIdentifier,
                ]);
            } catch (ScrapingException $e) {
                $errorMessage = sprintf('An error occurred during scraping: %s', $e->getMessage());
                $errors[$scraperIdentifier] = $errorMessage;
                $this->scraperLogger->error('Scraping failed.', [
                    'scraper' => $scraperIdentifier,
                    'exception' => $e,
                ]);
            }
        }

        $this->entityManager->flush();

        return [
            'results' => $results,
            'errors' => $errors,
        ];
    }

    /**
     * @param NewsArticleDTO[] $articles
     */
    private function saveArticles(array $articles, \App\Scraper\Enum\ScraperIdentifierEnum $source): void
    {
        foreach ($articles as $articleDTO) {
            // Avoid inserting duplicates based on the URL
            $existingArticle = $this->articleRepository->findOneByUrl($articleDTO->url);
            if ($existingArticle) {
                continue;
            }

            $article = new \App\Entity\Article();
            $article->setTitle($articleDTO->title);
            $article->setUrl($articleDTO->url);
            $article->setBody($articleDTO->body);
            $article->setSource($source);
            $article->setScrapedAt(new \DateTimeImmutable());

            $this->entityManager->persist($article);
        }
    }
}
