<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use Doctrine\DBAL\LockMode;

/**
 * Interface for the Article repository.
 *
 * This interface defines the contract for data access operations related to Articles.
 * Decoupling the service layer from the concrete repository implementation allows for
 * easier testing and more flexible architecture.
 */
interface ArticleRepositoryInterface
{
    /**
     * Finds all articles.
     *
     * @return Article[]
     */
    public function findAll(): array;

    /**
     * Finds a single article by its ID.
     *
     * @param int $id The ID of the article to find.
     * @return Article|null The Article entity if found, null otherwise.
     */
    public function find(int $id): ?Article;

    /**
     * Finds a single article by its URL.
     *
     * @param string $url The URL of the article to find.
     * @return Article|null The Article entity if found, null otherwise.
     */
    public function findOneByUrl(string $url): ?Article;

    /**
     * Persists an article to the database.
     *
     * @param Article $article The article to save.
     */
    public function save(Article $article): void;
}
