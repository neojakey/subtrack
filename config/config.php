<?php
/**
 * config.php — Bootstrap file
 * Load environment, instantiate core services, set error handling.
 */

// ── Load Config ────────────────────────────────────────────────────────────
require_once __DIR__ . '/../classes/Config.php';
Config::Load(dirname(__DIR__) . '/.env');

// ── Autoload classes ────────────────────────────────────────────────────────
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Classmap: load all files in /classes/
foreach (glob(dirname(__DIR__) . '/classes/*.php') as $classFile) {
    require_once $classFile;
}

// ── Error Reporting ─────────────────────────────────────────────────────────
if (Config::Get('APP_ENV', 'local') === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ── Session ──────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = str_starts_with(Config::Get('APP_URL', 'http://'), 'https://');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Apply theme from session/DB ──────────────────────────────────────────────
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}
