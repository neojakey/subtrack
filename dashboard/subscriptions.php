<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();

$subRepo = new SubscriptionRepository($pdo);
$catRepo = new CategoryRepository($pdo);

// Filters & pagination
$status = Input::get('status', '');
$categoryId = (int) Input::get('category', 0);
$cycle = Input::get('cycle', '');
$search = Input::get('search', '');
$sort = Input::get('sort', 'next_billing_date');
$dirParam = strtoupper(Input::get('dir', 'ASC'));
$page = max(1, (int) Input::get('page', 1));
$perPage = 20;

$subs = $subRepo->findAllByUser($userId, $status, $categoryId, $cycle, $search, $sort, $dirParam, $page, $perPage);
$total = $subRepo->countByUser($userId, $status, $categoryId, $cycle, $search);
$cats = $catRepo->findAll();
$catMap = $catRepo->findAllKeyed();

$pageTitle = 'Subscriptions — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Subscriptions</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            <?= $total ?> total
        </p>
    </div>
    <a href="<?= Config::Get('APP_URL') ?>/dashboard/add-subscription.php" class="btn-primary self-start sm:self-auto">
        <?= UIHelper::icon('plus', 'w-4 h-4') ?> Add subscription
    </a>
</div>

<!-- Filters -->
<form method="GET"
    class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
        <!-- Search -->
        <div class="flex-1">
            <input type="text" name="search" class="form-input" placeholder="Search subscriptions…"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <!-- Status -->
        <select name="status" class="form-select w-full sm:w-40">
            <option value="">All statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="paused" <?= $status === 'paused' ? 'selected' : '' ?>>Paused</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <!-- Category -->
        <select name="category" class="form-select w-full sm:w-48">
            <option value="">All categories</option>
            <?php foreach ($cats as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId === (int) $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Cycle -->
        <select name="cycle" class="form-select w-full sm:w-40">
            <option value="">All cycles</option>
            <option value="weekly" <?= $cycle === 'weekly' ? 'selected' : '' ?>>Weekly</option>
            <option value="monthly" <?= $cycle === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="quarterly" <?= $cycle === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
            <option value="biannual" <?= $cycle === 'biannual' ? 'selected' : '' ?>>Every 6 months</option>
            <option value="annual" <?= $cycle === 'annual' ? 'selected' : '' ?>>Annual</option>
        </select>
        <!-- Sort -->
        <select name="sort" class="form-select w-full sm:w-44">
            <option value="next_billing_date" <?= $sort === 'next_billing_date' ? 'selected' : '' ?>>Sort: Next due
            </option>
            <option value="amount" <?= $sort === 'amount' ? 'selected' : '' ?>>Sort: Amount</option>
            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Sort: Name A–Z</option>
            <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Sort: Date added</option>
        </select>
        <button type="submit" class="btn-primary flex-shrink-0">Filter</button>
        <?php if ($search || $status || $categoryId || $cycle): ?>
            <a href="<?= Config::Get('APP_URL') ?>/dashboard/subscriptions.php"
                class="btn-secondary flex-shrink-0">Clear</a>
        <?php endif; ?>
    </div>
</form>

<!-- Subscriptions grid -->
<?php if (empty($subs)): ?>
    <?= UIHelper::EmptyState(
        $search || $status || $categoryId || $cycle ? 'No subscriptions match your filters' : 'No subscriptions yet',
        $search ? 'Try a different search term or clear filters.' : 'Add your first subscription to start tracking your spending.',
        Config::Get('APP_URL') . '/dashboard/add-subscription.php',
        'Add subscription'
    ) ?>
<?php else: ?>
    <div class="space-y-3 mb-6">
        <?php foreach ($subs as $sub):
            $cat = $catMap[$sub['category_id']] ?? ['name' => 'Other', 'colour' => '#9CA3AF', 'icon' => 'ellipsis-horizontal'];
            echo UIHelper::SubscriptionCard($sub, $cat);
        endforeach; ?>
    </div>

    <?= UIHelper::Pagination($total, $page, $perPage, Config::Get('APP_URL') . '/dashboard/subscriptions.php?' . http_build_query(['status' => $status, 'category' => $categoryId, 'cycle' => $cycle, 'search' => $search, 'sort' => $sort])) ?>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>