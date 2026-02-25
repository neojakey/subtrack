<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();
$subRepo = new SubscriptionRepository($pdo);
$analytics = new AnalyticsService(new PaymentLogRepository($pdo), $subRepo);

$stats = $analytics->dashboardStats($userId);
$upcoming = $subRepo->findUpcoming($userId, 30);

$pageTitle = 'Dashboard — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Page title -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
            <?= date('l, j F Y') ?>
        </p>
    </div>
    <a href="<?= Config::Get('APP_URL') ?>/dashboard/add-subscription.php" class="btn-primary">
        <?= UIHelper::icon('plus', 'w-4 h-4') ?>
        Add subscription
    </a>
</div>

<!-- Summary widgets -->
<div class="dashboard-grid mb-8">
    <?php
    $monthly = $stats['monthly_total'];
    $annual = $stats['annual_total'];
    $count = $stats['active_count'];
    $next = $stats['next_payment'];

    echo UIHelper::SummaryWidget(
        'Monthly total',
        CurrencyHelper::formatGBP($monthly),
        'All active subscriptions',
        'blue',
        UIHelper::icon('currency-pound', 'w-6 h-6')
    );
    echo UIHelper::SummaryWidget(
        'Annual total',
        CurrencyHelper::formatGBP($annual),
        'Projected yearly spend',
        'indigo',
        UIHelper::icon('chart-bar', 'w-6 h-6')
    );
    echo UIHelper::SummaryWidget(
        'Active subscriptions',
        (string) $count,
        $count === 1 ? 'subscription tracked' : 'subscriptions tracked',
        'green',
        UIHelper::icon('list-bullet', 'w-6 h-6')
    );

    if ($next) {
        $nextAmt = CurrencyHelper::format((float) $next['amount'], $next['currency']);
        $nextDate = DateHelper::formatUK($next['next_billing_date']);
        $nextDue = DateHelper::dueLabel($next['next_billing_date']);
        echo UIHelper::SummaryWidget(
            'Next payment',
            $nextAmt,
            htmlspecialchars($next['name'], ENT_QUOTES, 'UTF-8') . ' · ' . $nextDue,
            'amber',
            UIHelper::icon('bell', 'w-6 h-6')
        );
    } else {
        echo UIHelper::SummaryWidget('Next payment', '—', 'No upcoming payments', 'amber', UIHelper::icon('bell', 'w-6 h-6'));
    }
    ?>
</div>

<!-- Main content: upcoming + recent -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

    <!-- Upcoming payments (2/3 width) -->
    <div class="xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Upcoming payments</h2>
            <a href="<?= Config::Get('APP_URL') ?>/dashboard/calendar.php"
                class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">
                View calendar →
            </a>
        </div>

        <?php if (empty($upcoming)): ?>
            <?= UIHelper::EmptyState(
                'No upcoming payments',
                'Add your first subscription to start tracking your spending.',
                Config::Get('APP_URL') . '/dashboard/add-subscription.php',
                'Add subscription'
            ) ?>
        <?php else: ?>
            <div class="space-y-3">
                <?php
                $catRepo = new CategoryRepository($pdo);
                $catMap = $catRepo->findAllKeyed();
                foreach ($upcoming as $sub):
                    $cat = $catMap[$sub['category_id']] ?? ['name' => 'Other', 'colour' => '#9CA3AF', 'icon' => 'ellipsis-horizontal'];
                    echo UIHelper::SubscriptionCard($sub, $cat);
                endforeach;
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick links (1/3 width) -->
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Quick actions</h2>
        <div class="space-y-2">
            <?php
            $links = [
                ['url' => 'dashboard/add-subscription.php', 'icon' => 'plus', 'label' => 'Add subscription', 'desc' => 'Track a new service'],
                ['url' => 'dashboard/subscriptions.php', 'icon' => 'list-bullet', 'label' => 'All subscriptions', 'desc' => 'Browse and manage'],
                ['url' => 'dashboard/calendar.php', 'icon' => 'calendar', 'label' => 'Calendar view', 'desc' => 'See upcoming payments'],
                ['url' => 'dashboard/analytics.php', 'icon' => 'chart-bar', 'label' => 'Analytics', 'desc' => 'Charts and totals'],
                ['url' => 'dashboard/payment-history.php', 'icon' => 'clock', 'label' => 'Payment history', 'desc' => 'Full log of payments'],
                ['url' => 'dashboard/reminders.php', 'icon' => 'bell', 'label' => 'Reminders', 'desc' => 'Email notification settings'],
            ];
            $base = Config::Get('APP_URL');
            foreach ($links as $link):
                ?>
                <a href="<?= $base ?>/<?= $link['url'] ?>"
                    class="flex items-center gap-4 p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-800 transition-all duration-200 group">
                    <div
                        class="w-9 h-9 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-colors flex-shrink-0">
                        <span class="text-blue-600 dark:text-blue-400">
                            <?= UIHelper::icon($link['icon'], 'w-5 h-5') ?>
                        </span>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-slate-900 dark:text-white">
                            <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($link['desc'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>