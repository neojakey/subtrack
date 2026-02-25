<?php
/**
 * UrlHelper.php — URL generation helpers
 */
class UrlHelper
{
    public static function base(string $path = ''): string
    {
        $base = rtrim(Config::Get('APP_URL', 'http://localhost:8000'), '/');
        return $base . '/' . ltrim($path, '/');
    }

    public static function canonical(string $path = ''): string
    {
        return self::base($path);
    }

    public static function asset(string $path): string
    {
        return self::base('assets/' . ltrim($path, '/'));
    }

    public static function redirect(string $path, int $code = 302): never
    {
        $url = str_starts_with($path, 'http') ? $path : self::base($path);
        header("Location: {$url}", true, $code);
        exit;
    }

    public static function current(): string
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return "{$scheme}://{$host}{$uri}";
    }

    public static function isActive(string $path): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return str_starts_with($uri, '/' . ltrim($path, '/'));
    }
}
