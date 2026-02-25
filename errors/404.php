<?php http_response_code(404);
require_once dirname(__DIR__) . '/config/config.php';
$pageTitle = '404 — Page not found — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php'; ?>
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center max-w-md">
        <div class="text-8xl font-black text-slate-200 dark:text-slate-800 mb-4 select-none">404</div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Page not found</h1>
        <p class="text-slate-500 dark:text-slate-400 mb-8">The page you're looking for doesn't exist or has been moved.
        </p>
        <a href="<?= Config::Get('APP_URL') ?>/dashboard/" class="btn-primary">Back to dashboard</a>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>