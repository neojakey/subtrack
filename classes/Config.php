<?php
/**
 * Config.php — Application Configuration Loader
 * Loads .env file and provides typed access to configuration values.
 */
class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    public static function Load(string $envPath = null): void
    {
        if (self::$loaded) {
            return;
        }

        if ($envPath === null) {
            $envPath = dirname(__DIR__) . '/.env';
        }

        if (!file_exists($envPath)) {
            throw new RuntimeException("Environment file not found: {$envPath}");
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Strip surrounding quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            self::$config[$key] = $value;
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        self::$loaded = true;
    }

    public static function Get(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function All(): array
    {
        return self::$config;
    }

    public static function IsProduction(): bool
    {
        return self::Get('APP_ENV', 'local') === 'production';
    }
}
