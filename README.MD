# Mini Project

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

Running the API

To enter the API container, run:
```bash
  docker compose exec api bash
```
Inside the container, install the required Symfony components and start the background worker with:
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