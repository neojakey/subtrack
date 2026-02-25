<?php
/**
 * Csrf.php — CSRF token management
 */
class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    public static function GetToken(): string
    {
        if (!Session::Has(self::TOKEN_KEY)) {
            Session::Put(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }
        $token = Session::Get(self::TOKEN_KEY);
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function TokenValue(): string
    {
        if (!Session::Has(self::TOKEN_KEY)) {
            Session::Put(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }
        return Session::Get(self::TOKEN_KEY);
    }

    public static function Validate(string $token = null): bool
    {
        $sessionToken = Session::Get(self::TOKEN_KEY);
        $submittedToken = $token ?? Input::raw('csrf_token', '');

        if (empty($sessionToken) || empty($submittedToken)) {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }

    public static function ValidateOrFail(): void
    {
        if (!self::Validate()) {
            http_response_code(403);
            require_once dirname(__DIR__) . '/errors/403.php';
            exit;
        }
    }

    public static function Regenerate(): void
    {
        Session::Put(self::TOKEN_KEY, bin2hex(random_bytes(32)));
    }
}
