<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Api\ArticleInputDTO;
use App\DTO\Api\ArticleOutputDTO;
use App\Entity\Article;
use App\Repository\ArticleRepositoryInterface;
use App\Scraper\Enum\ScraperIdentifierEnum;

class ArticleService implements ArticleServiceInterface
{
    /**
     * ArticleService constructor.
     *
     * @param ArticleRepositoryInterface $articleRepository The article repository.
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(): array
    {
        $articles = $this->articleRepository->findAll();

        return array_map(static fn ($article) => ArticleOutputDTO::fromEntity($article), $articles);
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?ArticleOutputDTO
    {
        $article = $this->articleRepository->find($id);

        return $article ? ArticleOutputDTO::fromEntity($article) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function create(ArticleInputDTO $articleDto): ArticleOutputDTO
    {
        $article = new Article();
        $article->setTitle($articleDto->title);
        $article->setUrl($articleDto->url);
        $article->setBody($articleDto->body);
        $article->setSource(ScraperIdentifierEnum::Api);
        $article->setScrapedAt(new \DateTimeImmutable());

        $this->articleRepository->save($article);

        return ArticleOutputDTO::fromEntity($article);
    }
}
