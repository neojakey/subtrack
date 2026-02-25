<?php
/**
 * Session.php — Session management helper
 */
class Session
{
    public static function Get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function Put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function Has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function Delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Store a flash message (retrieved once then removed).
     */
    public static function Flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            // Setting a flash
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        // Getting a flash
        $data = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $data;
    }

    public static function UserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function IsLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    public static function Destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}
