<?php
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use DI\Container;
use App\Router;
use App\Storage\MigrationRunner;

require __DIR__ . '/../vendor/autoload.php';

// Load .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Run migrations to ensure database is up to date
try {
    $runner = new MigrationRunner();
    $runner->run();
} catch (Exception $e) {
    // Log but don't stop - migrations might have already run
    error_log("Migration error: " . $e->getMessage());
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