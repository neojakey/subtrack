<?php
require_once dirname(__DIR__) . '/config/config.php';

// ── Validate state (CSRF) ───────────────────────────────────────────────────
if (empty($_GET['state']) || empty($_GET['code'])) {
    Logger::warning('Google callback: missing state or code.');
    http_response_code(403);
    require_once dirname(__DIR__) . '/errors/403.php';
    exit;
}

$authService = new GoogleAuthService();

if (!$authService->validateState($_GET['state'])) {
    Logger::warning('Google callback: state mismatch — possible CSRF.');
    http_response_code(403);
    require_once dirname(__DIR__) . '/errors/403.php';
    exit;
}

// ── Exchange code for profile ───────────────────────────────────────────────
try {
    $profile = $authService->handleCallback($_GET['code']);
} catch (Throwable $e) {
    Logger::error('Google callback error: ' . $e->getMessage());
    Session::Flash('error', 'Google sign-in failed. Please try again.');
    UrlHelper::redirect('auth/login.php');
}

$pdo = Database::getInstance();
$userRepo = new UserRepository($pdo);

$googleId = $profile['google_id'];
$email = $profile['email'];
$fullName = $profile['full_name'];
$avatarUrl = $profile['avatar_url'];

// ── Find or create user ─────────────────────────────────────────────────────
$user = $userRepo->findByGoogleId($googleId);

if ($user) {
    // Existing Google user — just log in
    $userRepo->updateLastLogin($user['id']);

} else {
    $existingByEmail = $userRepo->findByEmail($email);

    if ($existingByEmail) {
        // Link Google to existing local account
        $userRepo->linkGoogleAccount($existingByEmail['id'], $googleId, $avatarUrl);
        $userRepo->updateLastLogin($existingByEmail['id']);
        $user = $userRepo->findById($existingByEmail['id']);
        Logger::info("Google account linked to existing user: {$email}");

    } else {
        // Brand new user via Google
        // Show GDPR consent screen first
        Session::Put('pending_google_user', [
            'google_id' => $googleId,
            'email' => $email,
            'full_name' => $fullName,
            'avatar_url' => $avatarUrl,
        ]);
        UrlHelper::redirect('auth/gdpr-consent.php');
    }
}

// ── Create session ──────────────────────────────────────────────────────────
if ($user) {
    session_regenerate_id(true);
    Session::Put('user_id', $user['id']);
    Session::Put('user_name', $user['full_name']);
    Session::Put('user_email', $user['email']);
    Session::Put('user_role', $user['role']);
    Session::Put('user_avatar', $user['google_avatar_url'] ?? '');
    Session::Put('theme', $user['theme_preference'] ?? 'light');
    Session::Flash('toast_success', 'Welcome back, ' . $user['full_name'] . '!');
    UrlHelper::redirect('dashboard/');
}
