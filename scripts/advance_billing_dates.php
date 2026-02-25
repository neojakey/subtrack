#!/usr/bin/env php
<?php
/**
 * advance_billing_dates.php — Daily cron: advance billing dates after payment is due
 * Schedule: 5 0 * * * php /path/to/subtrack/scripts/advance_billing_dates.php
 *
 * When a subscription's next_billing_date is on or before today, we:
 * 1. Log the payment to payment_log
 * 2. Advance next_billing_date to the following billing cycle
 */
define('CRON', true);
require_once dirname(__DIR__) . '/config/config.php';

$start = microtime(true);
Logger::info('=== advance_billing_dates.php START ===');

$pdo = Database::getInstance();
$subRepo = new SubscriptionRepository($pdo);
$payRepo = new PaymentLogRepository($pdo);
$curRepo = new CurrencyRepository($pdo);

$overdue = $subRepo->findOverdue();
$count = 0;

foreach ($overdue as $sub) {
    try {
        // Convert to GBP for analytics
        $amountGbp = null;
        if ($sub['currency'] !== 'GBP') {
            $rate = $curRepo->getRate($sub['currency']);
            if ($rate !== false) {
                $amountGbp = round((float) $sub['amount'] * $rate, 2);
            }
        } else {
            $amountGbp = (float) $sub['amount'];
        }

        // Log the payment
        $payRepo->create(
            $sub['id'],
            $sub['user_id'],
            (float) $sub['amount'],
            $sub['currency'],
            $amountGbp,
            $sub['next_billing_date'],
            'Auto-logged by cron'
        );

        // Advance the billing date
        $subRepo->advanceBillingDate($sub['id']);
        $count++;

        Logger::info("Billed: [{$sub['id']}] {$sub['name']} ({$sub['currency']} {$sub['amount']}) next: {$sub['next_billing_date']}");
    } catch (Throwable $e) {
        Logger::error("Failed to advance sub [{$sub['id']}]: " . $e->getMessage());
    }
}

$elapsed = round(microtime(true) - $start, 3);
Logger::info("=== advance_billing_dates.php END — processed: {$count}, time: {$elapsed}s ===");
echo "[" . date('Y-m-d H:i:s') . "] Processed {$count} subscription(s) in {$elapsed}s\n";
