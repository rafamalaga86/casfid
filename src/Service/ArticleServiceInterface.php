<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Api\ArticleInputDTO;
use App\DTO\Api\ArticleOutputDTO;

interface ArticleServiceInterface
{
    /**
     * Retrieves all articles.
     *
     * @return ArticleOutputDTO[] an array of ArticleOutputDTO objects
     */
    public function findAll(): array;

    /**
     * Finds a single article by its ID.
     *
     * @param int $id the ID of the article to find
     *
     * @return ArticleOutputDTO|null the ArticleOutputDTO object if found, null otherwise
     */
    public function find(int $id): ?ArticleOutputDTO;

    /**
     * Creates a new article from the provided data.
     *
     * @param ArticleInputDTO $articleDto the data transfer object containing the article's data
     *
     * @return ArticleOutputDTO the created ArticleOutputDTO object
     */
    public function create(ArticleInputDTO $articleDto): ArticleOutputDTO;
}
