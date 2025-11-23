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
        private readonly EntityManagerInterface $em,
    ) {
        $this->repository = $this->em->getRepository(Article::class);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function find(int $id): ?Article
    {
        return $this->repository->find($id);
    }

    public function findOneByUrl(string $url): ?Article
    {
        return $this->repository->findOneBy(['url' => $url]);
    }

    public function save(Article $article): void
    {
        $this->em->persist($article);
        $this->em->flush();
    }

    /**
     * Removes an article from the database.
     *
     * @param Article $article the article to remove
     * @param bool    $flush   whether to immediately flush the changes to the database
     */
    public function remove(Article $article, bool $flush = true): void
    {
        $this->em->remove($article);
        if ($flush) {
            $this->em->flush();
        }
    }
}
