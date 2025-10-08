<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthorControllerTest extends WebTestCase
{
    public function testCreateAuthorSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/authors', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'Jane',
            'lastname'  => 'Austen',
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertTrue($client->getResponse()->headers->has('Location'));
        self::assertJson($client->getResponse()->getContent());
        self::assertArrayHasKey('id', json_decode($client->getResponse()->getContent(), true));
    }

    public function testCreateAuthorUnsupportedMedia(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/authors', [], [], ['CONTENT_TYPE' => 'text/plain'], 'firstname=Foo');

        self::assertResponseStatusCodeSame(415);
        self::assertStringContainsString('Unsupported', (string)$client->getResponse()->getContent());
    }

    public function testCreateAuthorTooLongFirstname(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/authors', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => str_repeat('A', 300),
            'lastname'  => 'Valid',
        ]));

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('too long', (string)$client->getResponse()->getContent());
    }

    public function testUpdateAuthorNotFound(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/authors/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'Linus',
            'lastname'  => 'Torvalds',
        ]));

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteAuthor(): void
    {
        $client = static::createClient();

        // 1. Create an author to delete
        $client->request('POST', '/api/authors', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'John',
            'lastname'  => 'Doe',
        ]));
        self::assertResponseStatusCodeSame(201);
        $authorUrl = $client->getResponse()->headers->get('Location');
        self::assertNotEmpty($authorUrl);

        // 2. Delete the author
        $client->request('DELETE', $authorUrl);
        self::assertResponseStatusCodeSame(204);

        // 3. Verify the author is gone
        $client->request('GET', $authorUrl);
        self::assertResponseStatusCodeSame(404);
    }

    public function testUpdateAuthorSuccess(): void
    {
        $client = static::createClient();

        // create author
        $client->request('POST', '/api/authors', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'Old',
            'lastname'  => 'Name',
        ]));
        self::assertResponseStatusCodeSame(201);
        $authorUrl = $client->getResponse()->headers->get('Location');
        self::assertNotEmpty($authorUrl);

        // update names
        $client->request('PUT', $authorUrl, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'New',
            'lastname'  => 'Surname',
        ]));
        self::assertResponseStatusCodeSame(204);

        // verify
        $client->request('GET', $authorUrl);
        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('New', $data['firstname']);
        self::assertSame('Surname', $data['lastname']);
    }

    public function testUpdateAuthorInvalidFields422(): void
    {
        $client = static::createClient();

        // create author
        $client->request('POST', '/api/authors', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'Valid',
            'lastname'  => 'Author',
        ]));
        self::assertResponseStatusCodeSame(201);
        $authorUrl = $client->getResponse()->headers->get('Location');
        self::assertNotEmpty($authorUrl);

        // blank firstname -> 422
        $client->request('PUT', $authorUrl, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => '',
            'lastname'  => 'Author',
        ]));
        self::assertResponseStatusCodeSame(422);

        // blank lastname -> 422
        $client->request('PUT', $authorUrl, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'Valid',
            'lastname'  => '',
        ]));
        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateAuthorExtraAttributes400(): void
    {
        $client = static::createClient();

        // extra attribute "foo" should be rejected (allow_extra_attributes=false)
        $client->request('POST', '/api/authors', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'Extra',
            'lastname'  => 'Attr',
            'foo'       => 'bar',
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertStringContainsString('Invalid request body', (string)$client->getResponse()->getContent());
    }
}
