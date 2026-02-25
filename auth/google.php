<?php
require_once dirname(__DIR__) . '/config/config.php';

if (Session::IsLoggedIn())
    UrlHelper::redirect('dashboard/');

$authService = new GoogleAuthService();

if (!$authService->isConfigured()) {
    Session::Flash('error', 'Google Sign-In is not configured. Please use email/password login.');
    UrlHelper::redirect('auth/login.php');
}

// Generate state, store in session, redirect to Google
$url = $authService->getAuthUrl();
header('Location: ' . $url);
exit;
