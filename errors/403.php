<?php http_response_code(403);
require_once dirname(__DIR__) . '/config/config.php';
$pageTitle = '403 — Forbidden — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php'; ?>
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center max-w-md">
        <div class="text-8xl font-black text-slate-200 dark:text-slate-800 mb-4 select-none">403</div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Access denied</h1>
        <p class="text-slate-500 dark:text-slate-400 mb-8">You don't have permission to view this page.</p>
        <a href="<?= Config::Get('APP_URL') ?>/dashboard/" class="btn-primary">Back to dashboard</a>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>