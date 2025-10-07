<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BookControllerTest extends WebTestCase
{
    private function createAuthorId(KernelBrowser $client): int
    {
        $client->request('POST', '/api/authors', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'Test',
            'lastname'  => 'Author',
        ]));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        return (int) $data['id'];
    }

    public function testCreateBookSuccess(): void
    {
        $client = static::createClient();
        $aid = $this->createAuthorId($client);

        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'Valid Title',
            'isbn'   => '9781234567897',
            'author' => $aid,
            'publicationYear' => 2023,
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertTrue($client->getResponse()->headers->has('Location'));
        self::assertArrayHasKey('id', json_decode($client->getResponse()->getContent(), true));
    }

    public function testCreateBookValidationErrors(): void
    {
        $client = static::createClient();
        $aid = $this->createAuthorId($client);

        // empty title
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => '',
            'isbn'   => '9781234567897',
            'author' => $aid,
            'publicationYear' => 2023,
        ]));
        self::assertResponseStatusCodeSame(422);

        // empty isbn
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'Valid',
            'isbn'   => '',
            'author' => $aid,
            'publicationYear' => 2023,
        ]));
        self::assertResponseStatusCodeSame(422);

        // author wrong type
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'Valid',
            'isbn'   => '9781234567897',
            'author' => 'abc',
        ]));
        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateBookDuplicateIsbn409(): void
    {
        $client = static::createClient();
        $aid = $this->createAuthorId($client);

        $payload = json_encode([
            'title'  => 'Dup',
            'isbn'   => '9999999999999',
            'author' => $aid,
            'publicationYear' => 2023,
        ]);

        // first insert ok
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        self::assertResponseStatusCodeSame(201);

        // duplicate
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        self::assertResponseStatusCodeSame(409);
    }

    public function testUpdateBookInvalidFields422(): void
    {
        $client = static::createClient();
        $aid = $this->createAuthorId($client);

        // create a book
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'To Update',
            'isbn'   => '1111111111111',
            'author' => $aid,
            'publicationYear' => 2023,
        ]));
        self::assertResponseStatusCodeSame(201);
        $bookId = (int) json_decode($client->getResponse()->getContent(), true)['id'];

        // blank title on update -> 422
        $client->request('PUT', "/api/books/$bookId", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => '',
            'isbn'  => '1111111111111',
        ]));
        self::assertResponseStatusCodeSame(422);

        // blank isbn on update -> 422
        $client->request('PUT', "/api/books/$bookId", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Ok',
            'isbn'  => '',
        ]));
        self::assertResponseStatusCodeSame(422);
    }
}
