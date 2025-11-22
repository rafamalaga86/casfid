<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ArticleRepository implements ArticleRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        $this->repository = $this->em->getRepository(Article::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Article
    {
        return $this->repository->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneByUrl(string $url): ?Article
    {
        return $this->repository->findOneBy(['url' => $url]);
    }

    /**
     * {@inheritDoc}
     */
    public function save(Article $article): void
    {
        $this->em->persist($article);
        $this->em->flush();
    }
}
