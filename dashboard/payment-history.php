<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();

$payRepo = new PaymentLogRepository($pdo);
$catRepo = new CategoryRepository($pdo);
$subRepo = new SubscriptionRepository($pdo);

$filters = [
    'from_date' => Input::get('from_date', ''),
    'to_date' => Input::get('to_date', ''),
    'subscription_id' => (int) Input::get('subscription_id', 0) ?: null,
    'category_id' => (int) Input::get('category_id', 0) ?: null,
];
$page = max(1, (int) Input::get('page', 1));
$perPage = 30;

$payments = $payRepo->findByUser($userId, array_filter($filters), $page, $perPage);
$total = $payRepo->countByUser($userId, array_filter($filters));
$cats = $catRepo->findAll();
$subs = $subRepo->findAllByUser($userId, '', 0, '', '', 'name', 'ASC', 1, 999);

$pageTitle = 'Payment History — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Payment history</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            <?= $total ?> records
        </p>
    </div>
    <a href="<?= Config::Get('APP_URL') ?>/dashboard/export.php?type=payments" class="btn-secondary">
        <?= UIHelper::icon('arrow-down-tray', 'w-4 h-4') ?> Export CSV
    </a>
</div>

<!-- Filters -->
<form method="GET" class="card mb-6">
    <div class="card-body">
        <div class="flex flex-wrap gap-3">
            <div>
                <label class="form-label">From date</label>
                <input type="date" name="from_date" class="form-input"
                    value="<?= htmlspecialchars($filters['from_date'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label class="form-label">To date</label>
                <input type="date" name="to_date" class="form-input"
                    value="<?= htmlspecialchars($filters['to_date'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label class="form-label">Subscription</label>
                <select name="subscription_id" class="form-select w-48">
                    <option value="">All subscriptions</option>
                    <?php foreach ($subs as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $filters['subscription_id'] == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select w-40">
                    <option value="">All categories</option>
                    <?php foreach ($cats as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="<?= Config::Get('APP_URL') ?>/dashboard/payment-history.php" class="btn-secondary">Clear</a>
            </div>
        </div>
    </div>
</form>

<!-- Payment table -->
<?php if (empty($payments)): ?>
    <?= UIHelper::EmptyState(
        'No payment records',
        'Payments are logged automatically when a billing date passes. Manual entries can be added when editing a subscription.',
        '',
        ''
    ) ?>
<?php else: ?>
    <div class="card overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/40">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500">Date</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500">Subscription</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500">Category</th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-500">Amount</th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-500">GBP equiv.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php foreach ($payments as $p): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                                <?= DateHelper::formatUK($p['paid_date']) ?>
                            </td>
                            <td class="px-5 py-3">
                                <a href="<?= Config::Get('APP_URL') ?>/dashboard/view-subscription.php?id=<?= $p['subscription_id'] ?>"
                                    class="font-medium text-slate-900 dark:text-white hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars($p['subscription_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <?php if (!empty($p['category_name'])): ?>
                                    <?= UIHelper::Badge($p['category_name'], $p['category_colour'] ?? '#9CA3AF') ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900 dark:text-white">
                                <?= CurrencyHelper::format((float) $p['amount'], $p['currency']) ?>
                            </td>
                            <td class="px-5 py-3 text-right text-slate-500">
                                <?= $p['amount_gbp'] ? CurrencyHelper::formatGBP((float) $p['amount_gbp']) : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?= UIHelper::Pagination($total, $page, $perPage, Config::Get('APP_URL') . '/dashboard/payment-history.php?' . http_build_query(array_filter($filters))) ?>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>