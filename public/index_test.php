<?php

/**
 * Dedicated front controller that forces the "test" environment.
 *
 * Needed because the built-in PHP server + symfony/runtime lets `.env`'s
 * APP_ENV=dev win (PHP's default variables_order doesn't surface shell env
 * vars into $_SERVER), which would otherwise load the dev DB. Use with:
 *
 *     php -S 127.0.0.1:8098 -t public public/index_test.php
 */

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

// Let PHP's built-in server serve real static files (CSS, JS, images) directly.
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($uri !== '/' && is_file(__DIR__.$uri)) {
    return false;
}

// Deterministically load env files so .env.test wins over .env / .env.local.
$root = dirname(__DIR__);
$dotenv = new Dotenv();
$dotenv->load($root.'/.env');
if (is_file($root.'/.env.local')) {
    $dotenv->load($root.'/.env.local');
}
$dotenv->load($root.'/.env.test');
if (is_file($root.'/.env.test.local')) {
    $dotenv->load($root.'/.env.test.local');
}

$kernel = new Kernel('test', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
