<?php
namespace App;

use Slim\App;
use Slim\Csrf\Guard;
use App\Controllers\UiController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;

class Router
{
    public static function configure(App $app): void
    {
        // Add CSRF middleware
        $responseFactory = $app->getResponseFactory();
        $csrf = new Guard($responseFactory);
        $csrf->setPersistentTokenMode(true);
        
        // Store CSRF in container for use by controllers
        $container = $app->getContainer();
        $container->set('csrf', $csrf);
        
        $csrf->setFailureHandler(function ($request, $handler) use ($responseFactory) {
            $response = $responseFactory->createResponse(403);
            $response->getBody()->write('<div class="alert alert-danger">CSRF validation failed. Please refresh and try again.</div>');
            return $response->withHeader('Content-Type', 'text/html');
        });
        
        // UI Routes (GET)
        $app->get('/', [UiController::class, 'home'])->add($csrf);
        $app->get('/settings', [UiController::class, 'settings'])->add($csrf);
        $app->get('/health', [ApiController::class, 'health']);
        
        // HTMX Fragment Routes (POST)
        $app->post('/next-scale', [ApiController::class, 'nextScale'])->add($csrf);
        $app->post('/attempt', [ApiController::class, 'attempt'])->add($csrf);
        $app->post('/reset-session', [ApiController::class, 'resetSession'])->add($csrf);
        $app->post('/new-day', [ApiController::class, 'newDay'])->add($csrf);
        
        // Settings Routes
        $app->post('/settings', [ApiController::class, 'saveSettings'])->add($csrf);
        $app->post('/scale/add', [ApiController::class, 'addScale'])->add($csrf);
        $app->post('/scale/delete/{id}', [ApiController::class, 'deleteScale'])->add($csrf);
        $app->post('/scale-type/add', [ApiController::class, 'addScaleType'])->add($csrf);
        $app->post('/scale-type/delete/{id}', [ApiController::class, 'deleteScaleType'])->add($csrf);
        $app->post('/set-type-filter', [ApiController::class, 'setTypeFilter'])->add($csrf);
        
        // Stats Fragment Routes (GET)
        $app->get('/stats-badges', [ApiController::class, 'statsBadges'])->add($csrf);
        $app->get('/scale-progress', [ApiController::class, 'scaleProgress'])->add($csrf);

        // Auth Routes
        $app->get('/login', [AuthController::class, 'loginPage'])->add($csrf);
        $app->get('/auth/google', [AuthController::class, 'login']);
        $app->get('/auth/callback', [AuthController::class, 'callback']);
        $app->get('/logout', [AuthController::class, 'logout']);
    }
}