<?php
namespace App\Domain;

use League\OAuth2\Client\Provider\Google;

class AuthService
{
    private ?Google $provider = null;

    public function getProvider(): Google
    {
        if ($this->provider === null) {
            $this->provider = new Google([
                'clientId' => $this->getEnv('GOOGLE_CLIENT_ID'),
                'clientSecret' => $this->getEnv('GOOGLE_CLIENT_SECRET'),
                'redirectUri' => $this->getEnv('GOOGLE_REDIRECT_URI'),
            ]);
        }
        return $this->provider;
    }

    private function getEnv(string $key): string
    {
        return $_ENV[$key] ?? getenv($key) ?: '';
    }

    public function getAuthorizationUrl(): string
    {
        $provider = $this->getProvider();
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['email', 'profile'],
        ]);

        // Store state for CSRF protection
        $_SESSION['oauth2_state'] = $provider->getState();

        return $authUrl;
    }

    public function handleCallback(string $code, string $state): ?array
    {
        // Verify state for CSRF protection
        if (empty($_SESSION['oauth2_state']) || $state !== $_SESSION['oauth2_state']) {
            unset($_SESSION['oauth2_state']);
            return null;
        }

        unset($_SESSION['oauth2_state']);

        try {
            $provider = $this->getProvider();
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            $user = $provider->getResourceOwner($token);
            $userData = $user->toArray();

            return [
                'email' => $userData['email'] ?? '',
                'name' => $userData['name'] ?? '',
                'picture' => $userData['picture'] ?? '',
            ];
        } catch (\Exception $e) {
            error_log('OAuth error: ' . $e->getMessage());
            return null;
        }
    }

    public function login(array $userData): void
    {
        $_SESSION['user'] = $userData;
        session_regenerate_id(true);
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    public function isAdmin(): bool
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        $adminEmails = $this->getEnv('ADMIN_EMAILS');
        if (empty($adminEmails)) {
            return false;
        }

        $emails = array_map('trim', explode(',', $adminEmails));
        $emails = array_map('strtolower', $emails);

        return in_array(strtolower($user['email']), $emails, true);
    }

    public function canManageScales(): bool
    {
        return $this->isAdmin();
    }
}
