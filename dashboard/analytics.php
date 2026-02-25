<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();

$analytics = new AnalyticsService(new PaymentLogRepository($pdo), new SubscriptionRepository($pdo));

$months = 12;
$monthlySpend = $analytics->monthlySpend($userId, $months);
$categoryData = $analytics->categoryBreakdown($userId);
$cycleData = $analytics->cycleBreakdown($userId);
$stats = $analytics->dashboardStats($userId);

// Prepare chart data
$monthLabels = array_column($monthlySpend, 'label');
$monthTotals = array_column($monthlySpend, 'total');
$catLabels = array_column($categoryData, 'name');
$catTotals = array_column($categoryData, 'total');
$catColours = array_column($categoryData, 'colour');

$pageTitle = 'Analytics — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Analytics</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Last 12 months of spending</p>
    </div>
</div>

<!-- Summary stat row -->
<div class="dashboard-grid mb-8">
    <?php
    echo UIHelper::SummaryWidget('Monthly average', CurrencyHelper::formatGBP(array_sum($monthTotals) / max(1, $months)), 'Based on actual payments', 'blue', UIHelper::icon('chart-bar', 'w-6 h-6'));
    echo UIHelper::SummaryWidget('12-month total', CurrencyHelper::formatGBP(array_sum($monthTotals)), 'Actual payments logged', 'indigo', UIHelper::icon('currency-pound', 'w-6 h-6'));
    echo UIHelper::SummaryWidget('Active subscriptions', (string) $stats['active_count'], 'currently tracked', 'green', UIHelper::icon('list-bullet', 'w-6 h-6'));
    echo UIHelper::SummaryWidget('Projected annual', CurrencyHelper::formatGBP($stats['annual_total']), 'Based on active subscriptions', 'amber', UIHelper::icon('arrow-trending-up', 'w-6 h-6'));
    ?>
</div>

<!-- Charts grid -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">

    <!-- Monthly spend bar chart (2/3) -->
    <div class="xl:col-span-2 card">
        <div class="card-header">
            <h2 class="font-semibold text-slate-900 dark:text-white">Monthly spending</h2>
            <span class="text-sm text-slate-400">Last
                <?= $months ?> months
            </span>
        </div>
        <div class="card-body">
            <div class="h-64 sm:h-80">
                <canvas id="chart-monthly" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Category doughnut (1/3) -->
    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold text-slate-900 dark:text-white">By category</h2>
        </div>
        <div class="card-body">
            <?php if (empty($categoryData)): ?>
                <p class="text-slate-400 text-sm text-center py-12">No payment data yet.</p>
            <?php else: ?>
                <div class="h-48">
                    <canvas id="chart-category" class="w-full h-full"></canvas>
                </div>
                <div class="mt-4 space-y-2 max-h-48 overflow-y-auto">
                    <?php foreach ($categoryData as $cat): ?>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full flex-shrink-0"
                                    style="background:<?= htmlspecialchars($cat['colour'], ENT_QUOTES, 'UTF-8') ?>"></span>
                                <span class="text-slate-700 dark:text-slate-300">
                                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <span class="font-semibold text-slate-900 dark:text-white">
                                <?= CurrencyHelper::formatGBP((float) $cat['total']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Billing cycle breakdown table -->
<div class="card mb-8">
    <div class="card-header">
        <h2 class="font-semibold text-slate-900 dark:text-white">By billing cycle</h2>
        <span class="text-sm text-slate-400">Normalised to monthly equivalent</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/40">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-500">Cycle</th>
                    <th class="text-right px-5 py-3 font-semibold text-slate-500">Monthly equiv.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php foreach ($cycleData as $row):
                    if ($row['total'] <= 0)
                        continue; ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-5 py-3 text-slate-700 dark:text-slate-300 font-medium">
                            <?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-900 dark:text-white">
                            <?= CurrencyHelper::formatGBP($row['total']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    (function () {
        var isDark = document.documentElement.classList.contains('dark');
        var textColour = isDark ? '#94a3b8' : '#64748b';
        var gridColour = isDark ? 'rgba(100,116,139,0.15)' : 'rgba(0,0,0,0.06)';

        // Monthly bar chart
        var monthCtx = document.getElementById('chart-monthly')?.getContext('2d');
        if (monthCtx) {
            new Chart(monthCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($monthLabels) ?>,
                    datasets: [{
                        label: 'Spend (GBP)',
                        data: <?= json_encode(array_map('floatval', $monthTotals)) ?>,
                        backgroundColor: 'rgba(59,130,246,0.85)',
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: gridColour }, ticks: { color: textColour, font: { size: 11 } } },
                        y: { grid: { color: gridColour }, ticks: { color: textColour, font: { size: 11 }, callback: v => '£' + v.toFixed(0) } }
                    }
                }
            });
        }

        // Category doughnut
        var catCtx = document.getElementById('chart-category')?.getContext('2d');
        if (catCtx && <?= json_encode(!empty($categoryData)) ?>) {
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($catLabels) ?>,
                    datasets: [{
                        data: <?= json_encode(array_map('floatval', $catTotals)) ?>,
                        backgroundColor: <?= json_encode($catColours) ?>,
                        borderWidth: 0,
                        hoverOffset: 4,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: { legend: { display: false } }
                }
            });
        }
    })();
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>