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

        return $this->createApiResponse($data);
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
            return $this->createApiResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $articleDto->toArray();

        return $this->createApiResponse($data);
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

        return $this->createApiResponse($articleDto->toArray(), Response::HTTP_CREATED);
    }

    /**
     * Updates an existing article.
     *
     * @param int             $id              the ID of the article to update
     * @param ArticleInputDTO $articleInputDTO the data transfer object containing the updated article's data
     *
     * @return JsonResponse a JSON response containing the updated article's data or a 404 error if not found
     */
    #[Route('/{id}', methods: ['PUT'], name: 'api_articles_update', requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] ArticleInputDTO $articleInputDTO): JsonResponse
    {
        $articleDto = $this->articleService->update($id, $articleInputDTO);

        if (!$articleDto) {
            return $this->createApiResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse($articleDto->toArray(), Response::HTTP_OK);
    }

    /**
     * Deletes an article by its ID.
     *
     * @param int $id the ID of the article to delete
     *
     * @return JsonResponse a JSON response with no content (204) on success, or a 404 error if not found
     */
    #[Route('/{id}', methods: ['DELETE'], name: 'api_articles_delete', requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $deleted = $this->articleService->delete($id);

        if (!$deleted) {
            return $this->createApiResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Creates a JSON response with standard encoding options.
     *
     * @param mixed $data    the response data
     * @param int   $status  the HTTP status code
     * @param array $headers an array of HTTP headers
     */
    private function createApiResponse(mixed $data, int $status = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        $response = new JsonResponse($data, $status, $headers);
        $response->setEncodingOptions(
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }
}
