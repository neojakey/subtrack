<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();
$subId = (int) Input::get('id', 0);

$subRepo = new SubscriptionRepository($pdo);
$sub = $subRepo->findById($subId, $userId);

if (!$sub) {
    UrlHelper::redirect('dashboard/subscriptions.php');
}

$catMap = (new CategoryRepository($pdo))->findAllKeyed();
$category = $catMap[$sub['category_id']] ?? ['name' => 'Other', 'colour' => '#9CA3AF', 'icon' => 'ellipsis-horizontal'];
$payments = (new PaymentLogRepository($pdo))->findBySubscription($subId, $userId);
$reminders = (new ReminderRepository($pdo))->findBySubscription($subId, $userId);

$pageTitle = htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') . ' — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';

$amount = CurrencyHelper::format((float) $sub['amount'], $sub['currency'] ?? 'GBP');
$monthlyEq = CurrencyHelper::formatGBP(DateHelper::monthlyEquivalent((float) $sub['amount'], $sub['billing_cycle']));
$cycleLabel = DateHelper::billingCycleLabel($sub['billing_cycle']);
$nextDate = DateHelper::formatUK($sub['next_billing_date']);
$dueLabel = DateHelper::dueLabel($sub['next_billing_date']);
$startDate = DateHelper::formatUK($sub['start_date']);
$logoHtml = UIHelper::SubscriptionLogo($sub, 48);
?>

<div class="max-w-3xl mx-auto">
    <!-- Back button -->
    <a href="<?= Config::Get('APP_URL') ?>/dashboard/subscriptions.php"
        class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white mb-6 transition-colors">
        <?= UIHelper::icon('chevron-left', 'w-4 h-4') ?> Back to subscriptions
    </a>

    <!-- Header -->
    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <?= $logoHtml ?>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                    <?= htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <?php if (!empty($sub['provider'])): ?>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">
                        <?= htmlspecialchars($sub['provider'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
                <div class="flex items-center gap-2 mt-1">
                    <?= UIHelper::StatusBadge($sub['status'] ?? 'active') ?>
                    <?= UIHelper::Badge($category['name'], $category['colour']) ?>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="<?= Config::Get('APP_URL') ?>/dashboard/edit-subscription.php?id=<?= $subId ?>"
                class="btn-secondary">
                <?= UIHelper::icon('pencil', 'w-4 h-4') ?> Edit
            </a>
            <?php if ($sub['status'] === 'active'): ?>
                <button onclick="pauseSubscription(<?= $subId ?>)" class="btn-secondary text-amber-600">
                    <?= UIHelper::icon('pause', 'w-4 h-4') ?> Pause
                </button>
            <?php elseif ($sub['status'] === 'paused'): ?>
                <button onclick="resumeSubscription(<?= $subId ?>)" class="btn-secondary text-emerald-600">
                    <?= UIHelper::icon('play', 'w-4 h-4') ?> Resume
                </button>
            <?php endif; ?>
            <button
                onclick="confirmDelete(<?= $subId ?>, '<?= htmlspecialchars(addslashes($sub['name']), ENT_QUOTES, 'UTF-8') ?>')"
                class="btn-secondary text-red-500">
                <?= UIHelper::icon('trash', 'w-4 h-4') ?> Delete
            </button>
        </div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 mb-1">Amount</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white">
                <?= $amount ?>
            </p>
            <p class="text-xs text-slate-400">
                <?= $cycleLabel ?>
            </p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 mb-1">Monthly equivalent</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white">
                <?= $monthlyEq ?>
            </p>
            <p class="text-xs text-slate-400">/ month (est.)</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 mb-1">Next billing</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white text-sm">
                <?= $nextDate ?>
            </p>
            <p class="text-xs text-amber-500 font-medium">
                <?= $dueLabel ?>
            </p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 mb-1">Active since</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white text-sm">
                <?= $startDate ?>
            </p>
            <p class="text-xs text-slate-400">start date</p>
        </div>
    </div>

    <!-- Details & Notes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-slate-900 dark:text-white">Details</h2>
            </div>
            <div class="card-body space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Billing cycle</span><span
                        class="font-medium">
                        <?= $cycleLabel ?>
                    </span></div>
                <div class="flex justify-between"><span class="text-slate-500">Currency</span><span class="font-medium">
                        <?= htmlspecialchars($sub['currency'] ?? 'GBP', ENT_QUOTES, 'UTF-8') ?>
                    </span></div>
                <div class="flex justify-between"><span class="text-slate-500">Auto-renews</span><span
                        class="font-medium">
                        <?= $sub['auto_renews'] ? 'Yes' : 'No' ?>
                    </span></div>
                <?php if (!empty($sub['end_date'])): ?>
                    <div class="flex justify-between"><span class="text-slate-500">End date</span><span class="font-medium">
                            <?= DateHelper::formatUK($sub['end_date']) ?>
                        </span></div>
                <?php endif; ?>
                <?php if (!empty($sub['url'])): ?>
                    <div class="flex justify-between items-center"><span class="text-slate-500">Manage link</span>
                        <a href="<?= htmlspecialchars($sub['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                            class="text-blue-600 hover:underline text-xs">Open →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-slate-900 dark:text-white">Notes</h2>
            </div>
            <div class="card-body text-sm text-slate-600 dark:text-slate-400">
                <?php if (!empty($sub['notes'])): ?>
                    <p>
                        <?= nl2br(htmlspecialchars($sub['notes'], ENT_QUOTES, 'UTF-8')) ?>
                    </p>
                <?php else: ?>
                    <p class="italic text-slate-400">No notes added.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Payment history -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-semibold text-slate-900 dark:text-white">Payment history</h2>
            <span class="text-sm text-slate-400">
                <?= count($payments) ?> payments
            </span>
        </div>
        <?php if (empty($payments)): ?>
            <div class="card-body">
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">No payments logged yet. Payments are
                    logged automatically when the billing date passes.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/40">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-slate-500 dark:text-slate-400">Date</th>
                            <th class="text-right px-5 py-3 font-semibold text-slate-500 dark:text-slate-400">Amount</th>
                            <th class="text-right px-5 py-3 font-semibold text-slate-500 dark:text-slate-400">GBP equiv.
                            </th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-500 dark:text-slate-400">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php foreach ($payments as $p): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-5 py-3 text-slate-700 dark:text-slate-300">
                                    <?= DateHelper::formatUK($p['paid_date']) ?>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-900 dark:text-white">
                                    <?= CurrencyHelper::format((float) $p['amount'], $p['currency']) ?>
                                </td>
                                <td class="px-5 py-3 text-right text-slate-500">
                                    <?= $p['amount_gbp'] ? CurrencyHelper::formatGBP((float) $p['amount_gbp']) : '—' ?>
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                    <?= htmlspecialchars($p['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?: '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>