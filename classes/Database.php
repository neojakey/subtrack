<?php
/**
 * Database.php — Singleton PDO connection
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = Config::Get('DB_HOST', 'localhost');
            $name = Config::Get('DB_NAME', 'subtrack');
            $user = Config::Get('DB_USER', 'root');
            $pass = Config::Get('DB_PASS', '');
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                Logger::write('Database connection failed: ' . $e->getMessage(), 'ERROR');
                http_response_code(500);
                require_once dirname(__DIR__) . '/errors/500.php';
                exit;
            }
        }

        return self::$instance;
    }

    /** Allow tests to inject a different PDO instance (e.g. test DB). */
    public static function setInstance(PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    /** Reset the singleton (used in tests). */
    public static function reset(): void
    {
        self::$instance = null;
    }

    // Prevent instantiation
    private function __construct()
    {
    }
    private function __clone()
    {
    }
}
