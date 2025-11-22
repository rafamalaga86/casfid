<?php

declare(strict_types=1);

namespace App\DTO\Api;

use Symfony\Component\Validator\Constraints as Assert;

class ArticleInputDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    public ?string $url = null;

    #[Assert\NotBlank]
    public ?string $body = null;
}
