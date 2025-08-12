<?php
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use DI\Container;
use App\Router;
use App\Storage\Migrations;

require __DIR__ . '/../vendor/autoload.php';

// Run migrations to ensure database is up to date
try {
    $migrations = new Migrations();
    $migrations->run();
} catch (Exception $e) {
    // Log but don't stop - migrations might have already run
    error_log("Migration check: " . $e->getMessage());
}

// Start session for CSRF protection
session_start();

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Configure routes
Router::configure($app);

// Run app
$app->run();