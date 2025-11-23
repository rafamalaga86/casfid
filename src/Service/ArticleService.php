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
     * @param ArticleRepositoryInterface $articleRepository the article repository
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function findAll(): array
    {
        $articles = $this->articleRepository->findAll();

        return array_map(static fn ($article) => ArticleOutputDTO::fromEntity($article), $articles);
    }

    public function find(int $id): ?ArticleOutputDTO
    {
        $article = $this->articleRepository->find($id);

        return $article ? ArticleOutputDTO::fromEntity($article) : null;
    }

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

    public function update(int $id, ArticleInputDTO $articleDto): ?ArticleOutputDTO
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            return null;
        }

        $article->setTitle($articleDto->title);
        $article->setUrl($articleDto->url);
        $article->setBody($articleDto->body);

        $this->articleRepository->save($article);

        return ArticleOutputDTO::fromEntity($article);
    }

    /**
     * Deletes an article by its ID.
     *
     * @param int $id the ID of the article to delete
     *
     * @return bool true if the article was deleted, false otherwise
     */
    public function delete(int $id): bool
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            return false;
        }

        $this->articleRepository->remove($article);

        return true;
    }
}
