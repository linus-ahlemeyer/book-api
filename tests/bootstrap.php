<?php

require dirname(__DIR__).'/vendor/autoload.php';

$projectDir = dirname(__DIR__);
$console = escapeshellarg($projectDir . '/bin/console');

// 1. Clear the test cache
passthru(sprintf('APP_ENV=test php %s cache:clear', $console));

// 2. Drop the test database (if it exists)
passthru(sprintf('APP_ENV=test php %s doctrine:database:drop --if-exists --force --quiet', $console));

// 3. Create the test database
passthru(sprintf('APP_ENV=test php %s doctrine:database:create --quiet', $console));

// 4. Run migrations to create the schema (without --quiet to see output)
passthru(sprintf('APP_ENV=test php %s doctrine:migrations:migrate --no-interaction', $console));
