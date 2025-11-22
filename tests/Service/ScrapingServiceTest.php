<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\NewsArticleDTO;
use App\Entity\Article;
use App\Repository\ArticleRepositoryInterface;
use App\Scraper\Enum\ScraperIdentifierEnum;
use App\Scraper\Exception\ScrapingException;
use App\Scraper\ScraperInterface;
use App\Service\ScrapingService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScrapingServiceTest extends KernelTestCase
{
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;
    /** @var ArticleRepositoryInterface&MockObject */
    private ArticleRepositoryInterface $articleRepository;
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock dependencies
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testScrapeSuccess(): void
    {
        // 1. Arrange
        $scraper1 = $this->createMock(ScraperInterface::class);
        $articleDto1 = new NewsArticleDTO('Title 1', 'http://example.com/1', 'Body 1');
        $scraper1->method('scrape')->willReturn([$articleDto1]);
        $scraper1->method('getIdentifier')->willReturn(ScraperIdentifierEnum::ElMundo);

        $scraper2 = $this->createMock(ScraperInterface::class);
        $articleDto2 = new NewsArticleDTO('Title 2', 'http://example.com/2', 'Body 2');
        $scraper2->method('scrape')->willReturn([$articleDto2]);
        $scraper2->method('getIdentifier')->willReturn(ScraperIdentifierEnum::ElPais);

        $scrapers = [$scraper1, $scraper2];

        // Expect repository to be checked for duplicates
        $this->articleRepository->expects($this->exactly(2))
            ->method('findOneByUrl')
            ->willReturnMap([
                ['http://example.com/1', null],
                ['http://example.com/2', null],
            ]);

        // Expect new articles to be persisted
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(Article::class));

        // Expect a single flush operation at the end
        $this->entityManager->expects($this->once())->method('flush');

        $service = new ScrapingService($scrapers, $this->logger, $this->entityManager, $this->articleRepository);

        // 2. Act
        $report = $service->scrape();

        // 3. Assert
        $this->assertCount(2, $report['results']);
        $this->assertCount(0, $report['errors']);
        $this->assertArrayHasKey(ScraperIdentifierEnum::ElMundo->value, $report['results']);
        $this->assertArrayHasKey(ScraperIdentifierEnum::ElPais->value, $report['results']);
        $this->assertEquals(1, $report['results'][ScraperIdentifierEnum::ElMundo->value]['count']);
        $this->assertEquals('Title 1', $report['results'][ScraperIdentifierEnum::ElMundo->value]['articles'][0]->title);
    }

    public function testScrapeWithNoArticlesFound(): void
    {
        // 1. Arrange
        $scraper = $this->createMock(ScraperInterface::class);
        $scraper->method('scrape')->willReturn([]);
        $scraper->method('getIdentifier')->willReturn(ScraperIdentifierEnum::ElMundo);

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('No articles found.', ['scraper' => ScraperIdentifierEnum::ElMundo->value]);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $service = new ScrapingService([$scraper], $this->logger, $this->entityManager, $this->articleRepository);

        // 2. Act
        $report = $service->scrape();

        // 3. Assert
        $this->assertCount(0, $report['results']);
        $this->assertCount(1, $report['errors']);
        $this->assertArrayHasKey(ScraperIdentifierEnum::ElMundo->value, $report['errors']);
        $this->assertEquals(
            'No articles were found. The website structure might have changed or the selector is wrong.',
            $report['errors'][ScraperIdentifierEnum::ElMundo->value]
        );
    }

    public function testScrapeWithScrapingException(): void
    {
        // 1. Arrange
        $scraper = $this->createMock(ScraperInterface::class);
        $exception = new ScrapingException('Test exception');
        $scraper->method('scrape')->willThrowException($exception);
        $scraper->method('getIdentifier')->willReturn(ScraperIdentifierEnum::ElMundo);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Scraping failed.', ['scraper' => ScraperIdentifierEnum::ElMundo->value, 'exception' => $exception]);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $service = new ScrapingService([$scraper], $this->logger, $this->entityManager, $this->articleRepository);

        // 2. Act
        $report = $service->scrape();

        // 3. Assert
        $this->assertCount(0, $report['results']);
        $this->assertCount(1, $report['errors']);
        $this->assertArrayHasKey(ScraperIdentifierEnum::ElMundo->value, $report['errors']);
        $this->assertEquals(
            'An error occurred during scraping: Test exception',
            $report['errors'][ScraperIdentifierEnum::ElMundo->value]
        );
    }

    public function testScrapeWithDuplicateArticle(): void
    {
        // 1. Arrange
        $scraper = $this->createMock(ScraperInterface::class);
        $articleDto = new NewsArticleDTO('Existing Title', 'http://example.com/existing', 'Body');
        $scraper->method('scrape')->willReturn([$articleDto]);
        $scraper->method('getIdentifier')->willReturn(ScraperIdentifierEnum::ElMundo);

        $this->articleRepository->expects($this->once())
            ->method('findOneByUrl')
            ->with('http://example.com/existing')
            ->willReturn(new Article());

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $service = new ScrapingService([$scraper], $this->logger, $this->entityManager, $this->articleRepository);

        // 2. Act
        $report = $service->scrape();

        // 3. Assert
        $this->assertCount(1, $report['results']);
        $this->assertCount(0, $report['errors']);
        $this->assertEquals(1, $report['results'][ScraperIdentifierEnum::ElMundo->value]['count']);
    }
}
