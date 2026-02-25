#!/usr/bin/env php
<?php
/**
 * send_reminders.php — Daily cron: send email reminders for upcoming billing dates
 * Schedule: 0 8 * * * php /path/to/subtrack/scripts/send_reminders.php
 */
define('CRON', true);
require_once dirname(__DIR__) . '/config/config.php';

$start = microtime(true);
Logger::info('=== send_reminders.php START ===');

$pdo = Database::getInstance();
$service = new ReminderService(new ReminderRepository($pdo));
$sent = $service->sendDueReminders();

$elapsed = round(microtime(true) - $start, 3);
Logger::info("=== send_reminders.php END — sent: {$sent}, time: {$elapsed}s ===");
echo "[" . date('Y-m-d H:i:s') . "] Sent {$sent} reminder(s) in {$elapsed}s\n";
