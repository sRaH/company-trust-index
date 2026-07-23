<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Load test environment variables.
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

// Ensure the test database exists and is reset to an empty schema.
// We drop+create rather than use migrations so the suite starts clean
// regardless of any data left by Cypress or a previous run; dama/doctrine-test-bundle
// then isolates each test inside a rolled-back transaction.
$createDbResult = 0;

$createDbOutput = [];
exec(
    sprintf('%s bin/console doctrine:database:create --if-not-exists -e test -n 2>&1', PHP_BINARY),
    $createDbOutput,
    $createDbResult,
);

// Drop existing entity tables; ignore errors (e.g. fresh DB with no schema yet).
exec(sprintf('%s bin/console doctrine:schema:drop --force -e test -n 2>&1', PHP_BINARY));

$schemaOutput = [];
$schemaResult = 0;
exec(
    sprintf('%s bin/console doctrine:schema:create -e test -n 2>&1', PHP_BINARY),
    $schemaOutput,
    $schemaResult,
);

if ($createDbResult !== 0) {
    fwrite(STDERR, "Failed to create test database:\n".implode("\n", $createDbOutput)."\n");
    exit(1);
}
if ($schemaResult !== 0) {
    fwrite(STDERR, "Failed to create test schema:\n".implode("\n", $schemaOutput)."\n");
    exit(1);
}
