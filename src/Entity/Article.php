<?php

declare(strict_types=1);

namespace App\Entity;

use App\Scraper\Enum\ScraperIdentifierEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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

    /**
     * Get the ID of the article.
     *
     * @return int|null the ID of the article
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the title of the article.
     *
     * @return string|null the title of the article
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title of the article.
     *
     * @param string $title the title to set
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the URL of the article.
     *
     * @return string|null the URL of the article
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set the URL of the article.
     *
     * @param string $url the URL to set
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the body content of the article.
     *
     * @return string|null the body content of the article
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Set the body content of the article.
     *
     * @param string $body the body content to set
     */
    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the source identifier of the article.
     *
     * @return ScraperIdentifierEnum|null the source identifier
     */
    public function getSource(): ?ScraperIdentifierEnum
    {
        return $this->source;
    }

    /**
     * Set the source identifier of the article.
     *
     * @param ScraperIdentifierEnum $source the source identifier to set
     */
    public function setSource(ScraperIdentifierEnum $source): static
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get the scraping date and time of the article.
     *
     * @return \DateTimeImmutable|null the scraping date and time
     */
    public function getScrapedAt(): ?\DateTimeImmutable
    {
        return $this->scrapedAt;
    }

    /**
     * Set the scraping date and time of the article.
     *
     * @param \DateTimeImmutable $scrapedAt the scraping date and time to set
     */
    public function setScrapedAt(\DateTimeImmutable $scrapedAt): static
    {
        $this->scrapedAt = $scrapedAt;

        return $this;
    }
}
