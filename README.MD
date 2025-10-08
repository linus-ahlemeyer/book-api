# Demo Project - Book API

This project is a Symfony-based API application set up with Docker for an isolated and easy-to-use development environment. It includes asynchronous background processing, fast database responses, and integration with the OpenLibrary API.

## Table of Contents
- [Environment Setup](#environment-setup)
- [Running the API](#running-the-api)
- [Symfony Components Used](#symfony-components-used)
- [Features](#features)

## Environment Setup

The project uses Docker to manage the development environment. To get started, run:

```bash
  docker compose up --build -d
```
This sets up PHP 8.4, rootless Nginx configuration, and Composer for dependency management.

The API interface will be available at localhost on port 81. 
Interactive documentation (Swagger UI) is exposed at http://localhost:81/api/doc

## Running the API

To enter the API container, run:
```bash
  docker compose exec api bash
```
Install the required Symfony components and start the background worker with:
```bash
  docker compose exec -it api bash -c "composer install && bin/console doctrine:migrations:migrate --no-interaction && bin/console messenger:consume async -vv"
```

## Symfony Components Used

This project uses the following Symfony components:

- `symfony/orm-pack` – Support for relational databases
- `symfony/maker-bundle` – Helps generate code quickly
- `symfony/serializer-pack` – For converting objects to/from JSON or XML
- `symfony/validator` – Validate entities and API requests
- `nelmio/api-doc-bundle` – Enhances REST API documentation
- `symfony/http-client` – For making HTTP requests (used for OpenLibrary API)
- `symfony/messenger` – Handles asynchronous message processing
- `symfony/doctrine-messenger` – Integration of Messenger with Doctrine

## Features

- **Fast Database Response:** Optimized queries for quick access to data.
- **Background Enrichment:** Uses OpenLibrary API to enrich data asynchronously.
- **Non-Blocking API Calls:** Controllers do not block while waiting for external API responses.

## Running Tests

```bash
docker compose exec -T api ./vendor/bin/phpunit
```

### Detailed test list

tests/Controller/AuthorControllerTest.php
- ✅ testCreateAuthorSuccess — creates an author (201, Location header, JSON id)
- ✅ testCreateAuthorUnsupportedMedia — 415 for non-JSON content type
- ✅ testCreateAuthorTooLongFirstname — 422 for firstname length
- ✅ testUpdateAuthorNotFound — 404 updating a non-existent author
- ✅ testDeleteAuthor — create, delete (204), then 404 on GET
- ✅ testUpdateAuthorSuccess — 204 update, then GET reflects changes
- ✅ testUpdateAuthorInvalidFields422 — 422 for blank firstname/lastname
- ✅ testCreateAuthorExtraAttributes400 — 400 for extra body fields

tests/Controller/BookControllerTest.php
- ✅ testCreateBookSuccess — creates a book (201, Location header, JSON id)
- ✅ testCreateBookValidationErrors — 422 for blank title/isbn, 422 for wrong author type
- ✅ testCreateBookDuplicateIsbn409 — 409 on duplicate ISBN
- ✅ testUpdateBookInvalidFields422 — 422 on blank title/isbn via PUT
- ✅ testCreateBookResolvesAuthorIdViaDenormalizer — author id resolved on create
- ✅ testCreateBookWithUnknownAuthorReturns404 — 404 when author id not found on create
- ✅ testUpdateBookChangesAuthorViaDenormalizer — author switched via PUT (204) and reflected in GET
- ✅ testUpdateBookWithUnknownAuthorReturns404 — 404 when switching to unknown author via PUT
- ✅ testCreateBookAcceptsAuthorIdAsString — accepts numeric string author id
- ✅ testDeleteBook — create, delete (204), then 404 on GET