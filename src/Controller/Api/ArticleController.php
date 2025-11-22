<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\Api\ArticleInputDTO;
use App\Service\ArticleServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/articles')]
class ArticleController extends AbstractController
{
    /**
     * ArticleController constructor.
     *
     * @param ArticleServiceInterface $articleService the article service
     */
    public function __construct(
        private readonly ArticleServiceInterface $articleService,
    ) {
    }

    /**
     * Retrieves a list of all articles.
     *
     * @return JsonResponse a JSON response containing a list of articles
     */
    #[Route(methods: ['GET'], path: '', name: 'api_articles_list')]
    public function getAll(): JsonResponse
    {
        $articles = $this->articleService->findAll();
        $data = array_map(fn ($article) => $article->toArray(), $articles);

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }

    /**
     * Retrieves a single article by its ID.
     *
     * @param int $id the ID of the article to retrieve
     *
     * @return JsonResponse a JSON response containing the article data or a 404 error if not found
     */
    #[Route('/{id}', methods: ['GET'], name: 'api_articles_get', requirements: ['id' => '\d+'])]
    public function get(int $id): JsonResponse
    {
        $articleDto = $this->articleService->find($id);

        if (!$articleDto) {
            return new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $articleDto->toArray();

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }

    /**
     * Creates a new article.
     *
     * @param ArticleInputDTO $articleInputDTO the data transfer object containing the new article's data
     *
     * @return JsonResponse a JSON response containing the newly created article's data
     */
    #[Route(methods: ['POST'], path: '', name: 'api_articles_create')]
    public function create(#[MapRequestPayload] ArticleInputDTO $articleInputDTO): JsonResponse
    {
        $articleDto = $this->articleService->create($articleInputDTO);

        $response = new JsonResponse($articleDto->toArray(), Response::HTTP_CREATED);
        $response->setEncodingOptions(
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }
}
