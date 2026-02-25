<?php
/**
 * Input.php — Request input helper
 */
class Input
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_GET[$key] ?? $default;
        return self::sanitize($value);
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $default;
        return self::sanitize($value);
    }

    public static function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    /** Get raw POST value without sanitization (e.g. for passwords) */
    public static function raw(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /** Get raw GET value without sanitization */
    public static function rawGet(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public static function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
