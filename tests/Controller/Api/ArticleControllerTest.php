<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Entity\Article;
use App\Scraper\Enum\ScraperIdentifierEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function clearDatabase(EntityManagerInterface $entityManager): void
    {
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            if (!$metadata->isMappedSuperclass) {
                $connection = $entityManager->getConnection();
                $platform = $connection->getDatabasePlatform();
                $connection->executeStatement($platform->getTruncateTableSQL($metadata->getTableName(), true));
            }
        }
    }

    private function createArticle(EntityManagerInterface $entityManager, string $title = 'Test Title', string $url = 'https://test.com/article', string $body = 'Test Body Content'): Article
    {
        $article = new Article();
        $article->setTitle($title);
        $article->setUrl($url);
        $article->setBody($body);
        $article->setSource(ScraperIdentifierEnum::ElMundo); // Default source for tests
        $article->setScrapedAt(new \DateTimeImmutable());

        $entityManager->persist($article);
        $entityManager->flush();

        return $article;
    }

    public function testGetAllArticles(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        // Create some articles
        $this->createArticle($entityManager, 'Article 1', 'https://example.com/art1');
        $this->createArticle($entityManager, 'Article 2', 'https://example.com/art2');

        $client->request('GET', '/api/articles');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertCount(2, $responseData);

        $this->assertEquals('Article 1', $responseData[0]['title']);
        $this->assertEquals('https://example.com/art1', $responseData[0]['url']);
        $this->assertEquals('Article 2', $responseData[1]['title']);
    }

    public function testGetSingleArticle(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        $article = $this->createArticle($entityManager, 'Unique Article', 'https://example.com/unique');

        $client->request('GET', '/api/articles/'.$article->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertEquals($article->getId(), $responseData['id']);
        $this->assertEquals($article->getTitle(), $responseData['title']);
        $this->assertEquals($article->getUrl(), $responseData['url']);
        $this->assertEquals($article->getBody(), $responseData['body']);
    }

    public function testGetNonExistentArticle(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        $client->request('GET', '/api/articles/99999'); // An ID that surely doesn't exist

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(['message' => 'Article not found'], $responseData);
    }

    public function testCreateArticle(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        $articleData = [
            'title' => 'New Article from Test',
            'url' => 'https://test.com/new-article',
            'body' => 'This is the body of the new article created by a functional test.',
        ];

        $client->request('POST', '/api/articles', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($articleData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($articleData['title'], $responseData['title']);
        $this->assertEquals($articleData['url'], $responseData['url']);
        $this->assertEquals($articleData['body'], $responseData['body']);

        // Verify that the article is actually in the database
        $articleRepository = $entityManager->getRepository(Article::class);
        $createdArticle = $articleRepository->find($responseData['id']);

        $this->assertNotNull($createdArticle);
        $this->assertEquals($articleData['title'], $createdArticle->getTitle());
        $this->assertEquals($articleData['url'], $createdArticle->getUrl());
        $this->assertEquals($articleData['body'], $createdArticle->getBody());
    }

    public function testUpdateArticleSuccessfully(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        // 1. Create an article to update
        $originalArticle = $this->createArticle($entityManager, 'Original Title', 'https://example.com/original', 'Original Body');

        $updatedArticleData = [
            'title' => 'Updated Title',
            'url' => 'https://example.com/updated',
            'body' => 'Updated Body Content',
        ];

        // 2. Send PUT request to update the article
        $client->request(
            'PUT',
            '/api/articles/'.$originalArticle->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedArticleData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // 3. Assert that the response contains the updated data
        $this->assertIsArray($responseData);
        $this->assertEquals($originalArticle->getId(), $responseData['id']);
        $this->assertEquals($updatedArticleData['title'], $responseData['title']);
        $this->assertEquals($updatedArticleData['url'], $responseData['url']);
        $this->assertEquals($updatedArticleData['body'], $responseData['body']);

        // 4. Verify that the article is actually updated in the database
        $client->request('GET', '/api/articles/'.$originalArticle->getId());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $fetchedData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($fetchedData);
        $this->assertEquals($originalArticle->getId(), $fetchedData['id']);
        $this->assertEquals($updatedArticleData['title'], $fetchedData['title']);
        $this->assertEquals($updatedArticleData['url'], $fetchedData['url']);
        $this->assertEquals($updatedArticleData['body'], $fetchedData['body']);
    }

    public function testUpdateNonExistentArticle(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        $nonExistentId = 99999; // An ID that surely doesn't exist

        $updatedArticleData = [
            'title' => 'Updated Title for NonExistent',
            'url' => 'https://example.com/nonexistent-updated',
            'body' => 'Updated Body Content for NonExistent',
        ];

        $client->request(
            'PUT',
            '/api/articles/'.$nonExistentId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedArticleData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(['message' => 'Article not found'], $responseData);
    }

    public function testUpdateArticleWithInvalidData(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        // Create an article to attempt to update
        $originalArticle = $this->createArticle($entityManager, 'Valid Title', 'https://example.com/valid', 'Valid Body');

        $invalidArticleData = [
            'title' => '', // Blank title
            'url' => 'invalid-url', // Invalid URL format
            'body' => '', // Blank body
        ];

        $client->request(
            'PUT',
            '/api/articles/'.$originalArticle->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidArticleData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertIsString($responseData['message']); // Assert that a message string exists
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertCount(3, $responseData['errors']);

        // Check for specific violation messages based on property paths
        $this->assertArrayHasKey('title', $responseData['errors']);
        $this->assertNotEmpty($responseData['errors']['title']);
        $this->assertArrayHasKey('url', $responseData['errors']);
        $this->assertNotEmpty($responseData['errors']['url']);
        $this->assertArrayHasKey('body', $responseData['errors']);
        $this->assertNotEmpty($responseData['errors']['body']);
    }

    public function testDeleteArticleSuccessfully(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        // Create an article to delete
        $articleToDelete = $this->createArticle($entityManager, 'Article to Delete', 'https://example.com/todelete', 'Content to be deleted');
        $articleId = $articleToDelete->getId();

        $client->request('DELETE', '/api/articles/'.$articleId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify that the article is no longer in the database
        $articleRepository = $entityManager->getRepository(Article::class);
        $deletedArticle = $articleRepository->find($articleId);

        $this->assertNull($deletedArticle);
    }

    public function testDeleteNonExistentArticle(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase($entityManager);

        $nonExistentId = 99999; // An ID that surely doesn't exist

        $client->request('DELETE', '/api/articles/'.$nonExistentId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(['message' => 'Article not found'], $responseData);
    }
}
