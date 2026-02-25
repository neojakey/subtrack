#!/usr/bin/env php
<?php
/**
 * refresh_exchange_rates.php — Daily cron: update exchange rates from API
 * Schedule: 0 6 * * * php /path/to/subtrack/scripts/refresh_exchange_rates.php
 */
define('CRON', true);
require_once dirname(__DIR__) . '/config/config.php';

$start = microtime(true);
Logger::info('=== refresh_exchange_rates.php START ===');

$pdo = Database::getInstance();
$service = new CurrencyService(new CurrencyRepository($pdo));
$result = $service->refreshRates();

$elapsed = round(microtime(true) - $start, 3);
Logger::info("=== refresh_exchange_rates.php END — success: " . ($result ? 'yes' : 'no') . ", time: {$elapsed}s ===");
echo "[" . date('Y-m-d H:i:s') . "] Rates refreshed: " . ($result ? 'yes' : 'no') . " in {$elapsed}s\n";
