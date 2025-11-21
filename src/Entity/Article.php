<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArticleRepository;
use App\Scraper\Enum\ScraperIdentifierEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_ARTICLE_URL', columns: ['url'])]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    #[ORM\Column(length: 255, enumType: ScraperIdentifierEnum::class)]
    private ?ScraperIdentifierEnum $source = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $scrapedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getSource(): ?ScraperIdentifierEnum
    {
        return $this->source;
    }

    public function setSource(ScraperIdentifierEnum $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getScrapedAt(): ?\DateTimeImmutable
    {
        return $this->scrapedAt;
    }

    public function setScrapedAt(\DateTimeImmutable $scrapedAt): static
    {
        $this->scrapedAt = $scrapedAt;

        return $this;
    }
}
