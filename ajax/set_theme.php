<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

header('Content-Type: application/json');

if (!Input::isPost()) {
    echo json_encode(['success' => false]);
    exit;
}

Csrf::ValidateOrFail();

$theme = Input::raw('theme', 'light');
if (!in_array($theme, ['light', 'dark'], true))
    $theme = 'light';

Session::Put('theme', $theme);

// Persist to DB if user has a record
$userId = Session::UserId();
if ($userId) {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('UPDATE users SET theme_preference = ? WHERE id = ?');
    $stmt->execute([$theme, $userId]);
}

echo json_encode(['success' => true]);
