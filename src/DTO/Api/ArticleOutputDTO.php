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
    /**
     * ArticleOutputDTO constructor.
     *
     * @param int                   $id        the unique identifier of the article
     * @param string                $title     the title of the article
     * @param string                $url       the URL of the article
     * @param string                $body      the cleaned body content of the article
     * @param ScraperIdentifierEnum $source    the source of the article
     * @param \DateTimeImmutable    $scrapedAt the date and time when the article was scraped
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $url,
        public readonly string $body,
        public readonly ScraperIdentifierEnum $source,
        public readonly \DateTimeImmutable $scrapedAt,
    ) {
    }

    /**
     * Creates an ArticleOutputDTO from an Article entity.
     *
     * @param Article $article the Article entity to convert
     *
     * @return self a new instance of ArticleOutputDTO
     */
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
            'body' => trim(preg_replace('/(\r\n|\n|\r){2,}/', "\n", $this->body)),
            'source' => $this->source->value,
            'scrapedAt' => $this->scrapedAt->format('Y-m-d H:i:s'),
        ];
    }
}
