<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Domain\AuthService;

class AuthController
{
    private AuthService $authService;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->authService = new AuthService();
    }

    public function loginPage(Request $request, Response $response): Response
    {
        // If already logged in, redirect to home
        if ($this->authService->isLoggedIn()) {
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }

        // Get CSRF from container
        $csrf = $this->container->get('csrf');
        $csrfNameValue = $csrf->getTokenName();
        $csrfTokenValue = $csrf->getTokenValue();

        ob_start();
        include dirname(__DIR__, 2) . '/views/login.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    public function login(Request $request, Response $response): Response
    {
        $authUrl = $this->authService->getAuthorizationUrl();

        return $response
            ->withHeader('Location', $authUrl)
            ->withStatus(302);
    }

    public function callback(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        // Check for error from OAuth provider
        if (isset($params['error'])) {
            $response->getBody()->write('Authentication error: ' . htmlspecialchars($params['error']));
            return $response->withStatus(400);
        }

        $code = $params['code'] ?? '';
        $state = $params['state'] ?? '';

        if (empty($code)) {
            $response->getBody()->write('Missing authorization code');
            return $response->withStatus(400);
        }

        $userData = $this->authService->handleCallback($code, $state);

        if ($userData === null) {
            $response->getBody()->write('Authentication failed. Please try again.');
            return $response->withStatus(400);
        }

        $this->authService->login($userData);

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        $this->authService->logout();

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }
}
