<?php

declare(strict_types=1);

namespace App\DTO\Api;

use App\Entity\Article;
use App\Scraper\Enum\ScraperIdentifierEnum;

/**
 * @phpstan-type ArticleOutputArray array{
 *   id: int,
 *   title: string,
 *   url: string,
 *   body: string,
 *   source: string,
 *   scrapedAt: string
 * }
 */
class ArticleOutputDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $url,
        public readonly string $body,
        public readonly ScraperIdentifierEnum $source,
        public readonly \DateTimeImmutable $scrapedAt
    ) {
    }

    public static function fromEntity(Article $article): self
    {
        return new self(
            $article->getId(),
            $article->getTitle(),
            $article->getUrl(),
            $article->getBody(),
            $article->getSource(),
            $article->getScrapedAt()
        );
    }

    /**
     * @return ArticleOutputArray
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'body' => $this->body,
            'source' => $this->source->value,
            'scrapedAt' => $this->scrapedAt->format('Y-m-d H:i:s'),
        ];
    }
}
