<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();

$type = Input::get('type', 'subscriptions');
$service = new ExportService(new SubscriptionRepository($pdo), new PaymentLogRepository($pdo));

match ($type) {
    'payments' => $service->streamPaymentHistoryCsv($userId),
    default => $service->streamSubscriptionsCsv($userId),
};
exit;
