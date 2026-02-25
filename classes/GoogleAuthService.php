<?php
/**
 * GoogleAuthService.php — Full Google OAuth2 via google/apiclient
 * Mirrors CarbonRegistryApp GoogleAuthService architecture.
 */
class GoogleAuthService
{
    private \Google\Client $client;

    public function __construct()
    {
        $this->client = new \Google\Client();
        $this->client->setClientId(Config::Get('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(Config::Get('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(Config::Get('GOOGLE_REDIRECT_URI'));
        $this->client->addScope('email');
        $this->client->addScope('profile');
        $this->client->setAccessType('online');
        $this->client->setPrompt('select_account');
    }

    public function generateState(): string
    {
        $state = bin2hex(random_bytes(16));
        Session::Put('oauth_state', $state);
        return $state;
    }

    public function validateState(string $state): bool
    {
        $sessionState = Session::Get('oauth_state');
        Session::Delete('oauth_state');
        if (empty($sessionState) || empty($state)) {
            return false;
        }
        return hash_equals($sessionState, $state);
    }

    public function getAuthUrl(): string
    {
        $state = $this->generateState();
        $this->client->setState($state);
        return $this->client->createAuthUrl();
    }

    /**
     * Exchange auth code for user profile.
     * @return array ['google_id', 'email', 'full_name', 'avatar_url']
     * @throws RuntimeException on failure
     */
    public function handleCallback(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            Logger::error('Google OAuth token error: ' . ($token['error_description'] ?? $token['error']));
            throw new RuntimeException('Failed to exchange Google authorisation code.');
        }

        $this->client->setAccessToken($token);

        $oauth2 = new \Google\Service\Oauth2($this->client);
        $userInfo = $oauth2->userinfo->get();

        return [
            'google_id' => $userInfo->getId(),
            'email' => $userInfo->getEmail(),
            'full_name' => $userInfo->getName(),
            'avatar_url' => $userInfo->getPicture() ?? '',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty(Config::Get('GOOGLE_CLIENT_ID')) && !empty(Config::Get('GOOGLE_CLIENT_SECRET'));
    }
}
