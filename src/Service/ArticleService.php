<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArticleRepositoryInterface;

class ArticleService implements ArticleServiceInterface
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
    ) {
    }
}
