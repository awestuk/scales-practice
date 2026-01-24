<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Domain\SessionService;
use App\Domain\StatsService;
use App\Domain\AuthService;
use App\Models\Scale;
use Slim\Csrf\Guard;

class UiController
{
    private SessionService $sessionService;
    private StatsService $statsService;
    private AuthService $authService;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->sessionService = new SessionService();
        $this->statsService = new StatsService();
        $this->authService = new AuthService();
    }
    
    public function home(Request $request, Response $response): Response
    {
        $session = $this->sessionService->getOrCreateActiveSession();
        $stats = $this->statsService->getSessionStats($session->id);
        $canStartNewDay = $this->sessionService->canStartNewDay();

        // Auth info for views
        $user = $this->authService->getUser();
        $isLoggedIn = $this->authService->isLoggedIn();
        $canManageScales = $this->authService->canManageScales();

        // Scale type filter info
        $scaleTypes = Scale::getTypes();
        $currentTypeFilter = $this->sessionService->getTypeFilter();

        // Get CSRF from container and generate tokens
        $csrf = $this->container->get('csrf');
        $csrfNameValue = $csrf->getTokenName();
        $csrfTokenValue = $csrf->getTokenValue();

        ob_start();
        include dirname(__DIR__, 2) . '/views/layout.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }
    
    public function settings(Request $request, Response $response): Response
    {
        // Require admin access to view settings
        if (!$this->authService->isAdmin()) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $config = $this->sessionService->getConfig();
        $scales = Scale::findAll();

        // Auth info for views
        $user = $this->authService->getUser();
        $isLoggedIn = $this->authService->isLoggedIn();
        $canManageScales = $this->authService->canManageScales();

        // Get CSRF from container and generate tokens
        $csrf = $this->container->get('csrf');
        $csrfNameValue = $csrf->getTokenName();
        $csrfTokenValue = $csrf->getTokenValue();

        ob_start();
        include dirname(__DIR__, 2) . '/views/settings.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }
}