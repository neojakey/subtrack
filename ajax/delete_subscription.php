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
$repo = new SubscriptionRepository($pdo);
$sub = $repo->findById($id, $userId);

if (!$sub) {
    echo json_encode(['success' => false, 'message' => 'Subscription not found.']);
    exit;
}

// Delete reminders first
(new ReminderRepository($pdo))->deleteBySubscription($id, $userId);

$repo->delete($id, $userId);

echo json_encode(['success' => true, 'message' => htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') . ' deleted.']);
