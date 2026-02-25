<?php
/**
 * Logger.php — Application logger
 */
class Logger
{
    private const LOG_FILE = 'app_debug.log';
    private const LEVELS = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];

    public static function write(string $message, string $level = 'INFO'): void
    {
        $level = strtoupper($level);
        if (!in_array($level, self::LEVELS, true)) {
            $level = 'INFO';
        }

        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/' . self::LOG_FILE;
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $uri = $_SERVER['REQUEST_URI'] ?? 'cli';
        $entry = "[{$timestamp}] [{$level}] [{$ip}] [{$uri}] {$message}" . PHP_EOL;

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function debug(string $message): void
    {
        self::write($message, 'DEBUG');
    }

    public static function info(string $message): void
    {
        self::write($message, 'INFO');
    }

    public static function warning(string $message): void
    {
        self::write($message, 'WARNING');
    }

    public static function error(string $message): void
    {
        self::write($message, 'ERROR');
    }

    public static function tail(int $lines = 100): array
    {
        $logFile = dirname(__DIR__) . '/logs/' . self::LOG_FILE;
        if (!file_exists($logFile)) {
            return [];
        }
        $allLines = file($logFile, FILE_IGNORE_NEW_LINES);
        return array_slice($allLines, -$lines);
    }
}
