<?php
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use DI\Container;
use App\Router;

require __DIR__ . '/../vendor/autoload.php';

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