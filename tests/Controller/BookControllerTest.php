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

    public function testCreateBookResolvesAuthorIdViaDenormalizer(): void
    {
        $client = static::createClient();
        $aid = $this->createAuthorId($client);

        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'Denorm Create',
            'isbn'   => '9781230001001',
            'author' => $aid,
            'publicationYear' => 2023,
        ]));
        self::assertResponseStatusCodeSame(201);

        $bookId = (int) json_decode($client->getResponse()->getContent(), true)['id'];

        // Verify author is resolved in details
        $client->request('GET', "/api/books/$bookId");
        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame($aid, $data['author']['id']);
    }

    public function testCreateBookWithUnknownAuthorReturns404(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'Unknown Author',
            'isbn'   => '9781230001002',
            'author' => 999999,
            'publicationYear' => 2023,
        ]));
        self::assertResponseStatusCodeSame(404);
    }

    public function testUpdateBookChangesAuthorViaDenormalizer(): void
    {
        $client = static::createClient();
        $aidA = $this->createAuthorId($client);
        $aidB = $this->createAuthorId($client);

        // create book with author A
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'To Reassign',
            'isbn'   => '9781230001003',
            'author' => $aidA,
            'publicationYear' => 2022,
        ]));
        self::assertResponseStatusCodeSame(201);
        $bookId = (int) json_decode($client->getResponse()->getContent(), true)['id'];

        // update: switch to author B
        $client->request('PUT', "/api/books/$bookId", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'To Reassign',
            'isbn'   => '9781230001003',
            'author' => $aidB,
            'publicationYear' => 2022,
        ]));
        self::assertResponseStatusCodeSame(204);

        // verify author changed
        $client->request('GET', "/api/books/$bookId");
        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame($aidB, $data['author']['id']);
    }

    public function testUpdateBookWithUnknownAuthorReturns404(): void
    {
        $client = static::createClient();
        $aid = $this->createAuthorId($client);

        // create book
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'Keep',
            'isbn'   => '9781230001004',
            'author' => $aid,
            'publicationYear' => 2021,
        ]));
        self::assertResponseStatusCodeSame(201);
        $bookId = (int) json_decode($client->getResponse()->getContent(), true)['id'];

        // update with non-existing author id -> 404 from denormalizer
        $client->request('PUT', "/api/books/$bookId", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'Keep',
            'isbn'   => '9781230001004',
            'author' => 999999,
            'publicationYear' => 2021,
        ]));
        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateBookAcceptsAuthorIdAsString(): void
    {
        $client = static::createClient();
        $aid = $this->createAuthorId($client);

        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'String Author Id',
            'isbn'   => '9781230001005',
            'author' => (string) $aid, // string digits
            'publicationYear' => 2023,
        ]));
        self::assertResponseStatusCodeSame(201);
    }

    public function testDeleteBook(): void
    {
        $client = static::createClient();
        $aid = $this->createAuthorId($client);

        // create a book to delete
        $client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title'  => 'To Delete',
            'isbn'   => '9781230002006',
            'author' => $aid,
            'publicationYear' => 2020,
        ]));
        self::assertResponseStatusCodeSame(201);
        $bookUrl = $client->getResponse()->headers->get('Location');
        self::assertNotEmpty($bookUrl);

        // delete the book
        $client->request('DELETE', $bookUrl);
        self::assertResponseStatusCodeSame(204);

        // verify 404 after deletion
        $client->request('GET', $bookUrl);
        self::assertResponseStatusCodeSame(404);
    }
}
