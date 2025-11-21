<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;

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
     * Finds a single article by its URL.
     */
    public function findOneByUrl(string $url): ?Article;
}
