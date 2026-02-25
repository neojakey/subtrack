<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();

$reminderRepo = new ReminderRepository($pdo);
$reminders = $reminderRepo->findByUser($userId);

$pageTitle = 'Reminders — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Reminders</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Email alerts before your subscriptions renew.</p>
    </div>
    <a href="<?= Config::Get('APP_URL') ?>/dashboard/add-subscription.php" class="btn-primary">
        <?= UIHelper::icon('plus', 'w-4 h-4') ?> Add subscription
    </a>
</div>

<?= UIHelper::Alert('info', 'Reminder emails are sent daily at 08:00. You can enable or disable reminders for each subscription from its edit page.') ?>

<div class="mt-6">
    <?php if (empty($reminders)): ?>
        <?= UIHelper::EmptyState(
            'No reminders set',
            'You can add email reminders when creating or editing a subscription.',
            Config::Get('APP_URL') . '/dashboard/subscriptions.php',
            'Browse subscriptions'
        ) ?>
    <?php else: ?>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/40">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-slate-500">Subscription</th>
                            <th class="text-center px-5 py-3 font-semibold text-slate-500">Days before</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-500">Next billing</th>
                            <th class="text-center px-5 py-3 font-semibold text-slate-500">Email</th>
                            <th class="text-center px-5 py-3 font-semibold text-slate-500">Active</th>
                            <th class="text-right px-5 py-3 font-semibold text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php foreach ($reminders as $r): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-5 py-3">
                                    <a href="<?= Config::Get('APP_URL') ?>/dashboard/view-subscription.php?id=<?= $r['subscription_id'] ?>"
                                        class="font-medium text-slate-900 dark:text-white hover:text-blue-600 transition-colors">
                                        <?= htmlspecialchars($r['subscription_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                    <p class="text-xs text-slate-400">
                                        <?= CurrencyHelper::format((float) $r['amount'], $r['currency']) ?>
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="badge bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                        <?= $r['days_before'] ?> day
                                        <?= $r['days_before'] > 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                                    <?= DateHelper::formatUK($r['next_billing_date']) ?>
                                    <span
                                        class="text-xs ml-1 <?= DateHelper::daysUntil($r['next_billing_date']) <= 7 ? 'text-amber-500' : 'text-slate-400' ?>">
                                        (
                                        <?= DateHelper::dueLabel($r['next_billing_date']) ?>)
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <?= $r['send_email'] ? UIHelper::icon('check-circle', 'w-5 h-5 text-emerald-500 mx-auto') : '—' ?>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <?= $r['is_active'] ? UIHelper::icon('check-circle', 'w-5 h-5 text-emerald-500 mx-auto') : UIHelper::icon('x-circle', 'w-5 h-5 text-slate-300 mx-auto') ?>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="<?= Config::Get('APP_URL') ?>/dashboard/edit-subscription.php?id=<?= $r['subscription_id'] ?>"
                                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>