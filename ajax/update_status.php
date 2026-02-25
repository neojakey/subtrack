<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

header('Content-Type: application/json');

$pdo = Database::getInstance();
$userId = Session::UserId();

if (!Input::isPost()) {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

Csrf::ValidateOrFail();

$id = (int) Input::raw('id', 0);
$status = Input::raw('status', '');

if (!in_array($status, ['active', 'paused', 'cancelled'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

$repo = new SubscriptionRepository($pdo);
$sub = $repo->findById($id, $userId);

if (!$sub) {
    echo json_encode(['success' => false, 'message' => 'Subscription not found.']);
    exit;
}

$repo->updateStatus($id, $userId, $status);

$labels = ['active' => 'resumed', 'paused' => 'paused', 'cancelled' => 'cancelled'];
echo json_encode(['success' => true, 'message' => htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') . ' ' . $labels[$status] . '.']);
