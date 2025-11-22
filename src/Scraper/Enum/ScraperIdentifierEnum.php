<?php

declare(strict_types=1);

namespace App\Scraper\Enum;

/**
 * Enum for scraper identifiers.
 *
 * Using a backed enum ensures type safety for scraper identifiers and provides
 * a single source of truth for the string values that might be stored in a database.
 */
enum ScraperIdentifierEnum: string
{
    case ElMundo = 'elmundo';
    case ElPais = 'elpais';
    case Api = 'api';
}
