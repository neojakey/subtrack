<?php
/**
 * header.php — Global site header and navigation
 *
 * Variables expected from calling page:
 *   $pageTitle   (string)  — <title> content
 *   $pageDesc    (string)  — meta description (optional)
 *   $bodyClass   (string)  — extra body CSS classes (optional)
 */
if (!isset($pageTitle))
    $pageTitle = 'SubTrack — Subscription Tracker';
if (!isset($pageDesc))
    $pageDesc = 'Know exactly what you spend on subscriptions every month.';
$isLoggedIn = Session::IsLoggedIn();
$userName = Session::Get('user_name', '');
$userAvatar = Session::Get('user_avatar', '');
$theme = Session::Get('theme', 'light');
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';

$baseUrl = Config::Get('APP_URL', 'http://localhost:8000');
$cssUrl = $baseUrl . '/assets/css/output.css';
?>
<!DOCTYPE html>
<html lang="en" class="<?= $theme === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
    </title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="theme-color" content="#1D4ED8">

    <!-- Canonical -->
    <link rel="canonical" href="<?= UrlHelper::current() ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl ?>/assets/images/logo.svg">

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?= $cssUrl ?>">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Dark mode: apply class before paint to prevent flash -->
    <script>
        (function () {
            const t = localStorage.getItem('subtrack_theme') || '<?= $theme ?>';
            if (t === 'dark') document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })();
    </script>
</head>

<body class="<?= $isLoggedIn ? 'flex min-h-screen' : '' ?> <?= $bodyClass ?? '' ?>">

    <?php if ($isLoggedIn): ?>
        <!-- ── Sidebar Navigation (desktop) ─────────────────────────────────────────── -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-slate-900 border-r border-slate-100 dark:border-slate-800 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300"
            aria-label="Sidebar navigation">

            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-100 dark:border-slate-800">
                <div
                    class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="font-bold text-lg text-slate-900 dark:text-white tracking-tight">SubTrack</span>
            </div>

            <!-- Nav links -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
                <?php
                $navItems = [
                    ['url' => 'dashboard/', 'match' => '/dashboard/index', 'icon' => 'home', 'label' => 'Dashboard'],
                    ['url' => 'dashboard/subscriptions.php', 'match' => '/dashboard/subscriptions', 'icon' => 'list-bullet', 'label' => 'Subscriptions'],
                    ['url' => 'dashboard/calendar.php', 'match' => '/dashboard/calendar', 'icon' => 'calendar', 'label' => 'Calendar'],
                    ['url' => 'dashboard/analytics.php', 'match' => '/dashboard/analytics', 'icon' => 'chart-bar', 'label' => 'Analytics'],
                    ['url' => 'dashboard/payment-history.php', 'match' => '/dashboard/payment-history', 'icon' => 'clock', 'label' => 'Payment History'],
                    ['url' => 'dashboard/reminders.php', 'match' => '/dashboard/reminders', 'icon' => 'bell', 'label' => 'Reminders'],
                ];
                foreach ($navItems as $item):
                    $isActive = str_contains($currentPath, $item['match']);
                    $cls = $isActive ? 'nav-link-active' : 'nav-link';
                    ?>
                    <a href="<?= $baseUrl . '/' . $item['url'] ?>" class="<?= $cls ?>">
                        <?= UIHelper::icon($item['icon'], 'w-5 h-5 flex-shrink-0') ?>
                        <span>
                            <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </a>
                <?php endforeach; ?>

                <?php if (Session::Get('user_role') === 'admin'): ?>
                    <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800">
                        <p class="px-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">
                            Admin</p>
                        <a href="<?= $baseUrl ?>/admin/" class="nav-link">
                            <?= UIHelper::icon('shield-check', 'w-5 h-5') ?><span>Admin</span>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>

            <!-- Bottom: user + add button -->
            <div class="p-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                <a href="<?= $baseUrl ?>/dashboard/add-subscription.php" class="btn-primary w-full justify-center">
                    <?= UIHelper::icon('plus', 'w-4 h-4') ?>
                    Add Subscription
                </a>
                <div
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <?php if ($userAvatar): ?>
                        <img src="<?= htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8') ?>" alt=""
                            class="w-8 h-8 rounded-full">
                    <?php else: ?>
                        <div
                            class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            <?= strtoupper(substr($userName ?: 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                            <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                    <a href="<?= $baseUrl ?>/dashboard/profile.php" class="btn-icon flex-shrink-0" title="Settings">
                        <?= UIHelper::icon('cog', 'w-4 h-4') ?>
                    </a>
                </div>
            </div>
        </aside>

        <!-- ── Mobile top bar ────────────────────────────────────────────────────── -->
        <div
            class="lg:hidden fixed top-0 inset-x-0 z-40 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between px-4 h-14">
            <button id="sidebar-toggle" class="btn-icon" aria-label="Toggle navigation">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <span class="font-bold text-slate-900 dark:text-white">SubTrack</span>
            <a href="<?= $baseUrl ?>/dashboard/add-subscription.php" class="btn-icon text-blue-600">
                <?= UIHelper::icon('plus', 'w-5 h-5') ?>
            </a>
        </div>

        <!-- Sidebar overlay (mobile) -->
        <div id="sidebar-overlay" class="lg:hidden fixed inset-0 z-20 bg-black/40 backdrop-blur-sm hidden"
            onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full');this.classList.toggle('hidden')">
        </div>

        <!-- ── Main content wrapper ──────────────────────────────────────────────── -->
        <div class="flex-1 flex flex-col lg:pl-64 min-w-0">
            <main class="flex-1 px-4 sm:px-6 lg:px-8 pt-20 lg:pt-8 pb-12">

                <!-- Flash messages -->
                <?php
                $flashSuccess = Session::Flash('success');
                $flashError = Session::Flash('error');
                $flashInfo = Session::Flash('info');
                if ($flashSuccess): ?>
                    <div class="mb-6">
                        <?= UIHelper::Alert('success', $flashSuccess) ?>
                    </div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <div class="mb-6">
                        <?= UIHelper::Alert('error', $flashError) ?>
                    </div>
                <?php endif; ?>
                <?php if ($flashInfo): ?>
                    <div class="mb-6">
                        <?= UIHelper::Alert('info', $flashInfo) ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- ── Public nav ─────────────────────────────────────────────────────────── -->
                <nav
                    class="sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800">
                    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
                        <a href="<?= $baseUrl ?>/"
                            class="flex items-center gap-2 font-bold text-lg text-slate-900 dark:text-white">
                            <div
                                class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            SubTrack
                        </a>
                        <div class="flex items-center gap-3">
                            <button id="theme-toggle-public" class="btn-icon" aria-label="Toggle theme">
                                <svg id="icon-sun" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                </svg>
                                <svg id="icon-moon" class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                </svg>
                            </button>
                            <a href="<?= $baseUrl ?>/auth/login.php" class="btn-secondary text-sm">Log in</a>
                            <a href="<?= $baseUrl ?>/auth/register.php" class="btn-primary text-sm">Get started</a>
                        </div>
                    </div>
                </nav>
                <main>
                <?php endif; ?>