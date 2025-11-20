<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * A Data Transfer Object representing a single news article.
 *
 * Using a DTO makes contracts between different parts of the system (like scrapers and services)
 * explicit and type-safe. It prevents bugs that can arise from using generic arrays with 'magic' keys.
 */
final readonly class NewsArticleDTO
{
    public function __construct(
        /**
         * The title of the news article.
         */
        public string $title,

        /**
         * The URL pointing to the full news article.
         */
        public string $url,

        /**
         * The full body content of the news article.
         */
        public string $body,
    ) {
    }
}
