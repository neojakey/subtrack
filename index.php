<?php
require_once 'config/config.php';

// Entry point — redirect to dashboard or homepage
if (Session::IsLoggedIn()) {
    UrlHelper::redirect('dashboard/');
} else {
    // If a landing page exists, include it; otherwise redirect to login
    if (file_exists(__DIR__ . '/index_landing.php')) {
        require_once __DIR__ . '/index_landing.php';
    } else {
        UrlHelper::redirect('auth/login.php');
    }
}
