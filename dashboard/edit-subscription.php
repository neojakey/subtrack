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

$catRepo = new CategoryRepository($pdo);
$categories = $catRepo->findAll();
$catMap = $catRepo->findAllKeyed();

$reminderRepo = new ReminderRepository($pdo);
$existingReminder = $reminderRepo->findBySubscription($subId, $userId);
$reminder = $existingReminder[0] ?? null;

$errors = [];

if (Input::isPost()) {
    Csrf::ValidateOrFail();

    $data = [
        'name' => trim(Input::raw('name', '')),
        'provider' => trim(Input::raw('provider', '')),
        'logo_url' => trim(Input::raw('logo_url', '')),
        'category_id' => (int) Input::raw('category_id', 0),
        'amount' => Input::raw('amount', ''),
        'currency' => strtoupper(Input::raw('currency', 'GBP')),
        'billing_cycle' => Input::raw('billing_cycle', 'monthly'),
        'billing_day' => Input::raw('billing_day', null),
        'start_date' => Input::raw('start_date', date('Y-m-d')),
        'next_billing_date' => Input::raw('next_billing_date', ''),
        'end_date' => Input::raw('end_date', null) ?: null,
        'auto_renews' => !empty($_POST['auto_renews']) ? 1 : 0,
        'url' => trim(Input::raw('url', '')),
        'notes' => trim(Input::raw('notes', '')),
    ];

    if (empty($data['name']))
        $errors['name'] = 'Service name is required.';
    if ($data['category_id'] <= 0)
        $errors['category_id'] = 'Please select a category.';
    if (!is_numeric($data['amount']) || (float) $data['amount'] <= 0)
        $errors['amount'] = 'Please enter a valid amount.';
    if (empty($data['next_billing_date']))
        $errors['next_billing_date'] = 'Next billing date is required.';

    if (empty($errors)) {
        $subRepo->update($subId, $userId, $data);

        // Handle reminder
        $reminderEnabled = !empty($_POST['reminder_enabled']);
        $reminderDays = (int) Input::raw('reminder_days', 3);

        if ($reminder) {
            if ($reminderEnabled) {
                $reminderRepo->update($reminder['id'], $userId, $reminderDays, true, true);
            } else {
                $reminderRepo->delete($reminder['id'], $userId);
            }
        } elseif ($reminderEnabled) {
            $reminderRepo->create($subId, $userId, $reminderDays);
        }

        Session::Flash('toast_success', 'Subscription updated.');
        UrlHelper::redirect("dashboard/view-subscription.php?id={$subId}");
    }

    // Re-merge errors with posted data
    $sub = array_merge($sub, $data);
}

$pageTitle = 'Edit ' . htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') . ' — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="<?= Config::Get('APP_URL') ?>/dashboard/view-subscription.php?id=<?= $subId ?>" class="btn-icon">
            <?= UIHelper::icon('chevron-left', 'w-5 h-5') ?>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit subscription</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Editing amounts does not alter past payment history.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" class="space-y-6" novalidate>
                <?= Csrf::GetToken() ?>

                <div class="space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Service details</h2>
                    <div>
                        <label for="name" class="form-label">Service name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name"
                            class="form-input <?= isset($errors['name']) ? 'border-red-500' : '' ?>"
                            value="<?= htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <p class="form-error">
                                <?= $errors['name'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="provider" class="form-label">Provider</label>
                        <input type="text" id="provider" name="provider" class="form-input"
                            value="<?= htmlspecialchars($sub['provider'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="logo_url" class="form-label">Logo URL</label>
                        <input type="url" id="logo_url" name="logo_url" class="form-input"
                            value="<?= htmlspecialchars($sub['logo_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="url" class="form-label">Manage subscription URL</label>
                        <input type="url" id="url" name="url" class="form-input"
                            value="<?= htmlspecialchars($sub['url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <!-- Category -->
                <div class="space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Category</h2>
                    <?php if (isset($errors['category_id'])): ?>
                        <p class="form-error">
                            <?= $errors['category_id'] ?>
                        </p>
                    <?php endif; ?>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                        <?php foreach ($categories as $cat): ?>
                            <label
                                class="cat-option <?= (int) $sub['category_id'] === (int) $cat['id'] ? 'cat-option-active' : '' ?>">
                                <input type="radio" name="category_id" value="<?= $cat['id'] ?>" class="sr-only"
                                    <?= (int) $sub['category_id'] === (int) $cat['id'] ? 'checked' : '' ?>
                                onchange="this.closest('.grid').querySelectorAll('.cat-option').forEach(e=>e.classList.remove('cat-option-active'));this.parentElement.classList.add('cat-option-active')">
                                <span style="color:<?= htmlspecialchars($cat['colour'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= UIHelper::icon($cat['icon'] ?? 'ellipsis-horizontal', 'w-6 h-6') ?>
                                </span>
                                <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-tight">
                                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Amount & Cycle -->
                <div class="space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Amount & billing cycle</h2>
                    <div class="flex gap-3">
                        <div class="w-28 flex-shrink-0">
                            <label for="currency" class="form-label">Currency</label>
                            <select id="currency" name="currency" class="form-select">
                                <?php foreach (CurrencyHelper::getSupportedCurrencies() as $cur): ?>
                                    <option value="<?= $cur ?>" <?= ($sub['currency'] ?? 'GBP') === $cur ? 'selected' : '' ?>>
                                        <?= CurrencyHelper::getSymbol($cur) ?>
                                        <?= $cur ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label for="amount" class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input type="number" id="amount" name="amount"
                                class="form-input <?= isset($errors['amount']) ? 'border-red-500' : '' ?>"
                                value="<?= number_format((float) $sub['amount'], 2, '.', '') ?>" step="0.01" min="0.01"
                                required>
                            <?php if (isset($errors['amount'])): ?>
                                <p class="form-error">
                                    <?= $errors['amount'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <?php foreach (['weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'biannual' => 'Every 6 months', 'annual' => 'Annual'] as $val => $lbl): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="billing_cycle" value="<?= $val ?>" class="sr-only"
                                    <?= ($sub['billing_cycle'] ?? 'monthly') === $val ? 'checked' : '' ?>
                                onchange="document.querySelectorAll('.cycle-pill').forEach(e=>e.classList.replace('pill-btn-active','pill-btn'));this.nextElementSibling.classList.replace('pill-btn','pill-btn-active')">
                                <span
                                    class="cycle-pill <?= ($sub['billing_cycle'] ?? 'monthly') === $val ? 'pill-btn-active' : 'pill-btn' ?>">
                                    <?= $lbl ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Dates -->
                <div class="space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Dates</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="form-label">Start date</label>
                            <input type="date" id="start_date" name="start_date" class="form-input"
                                value="<?= htmlspecialchars($sub['start_date'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="next_billing_date" class="form-label">Next billing date <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="next_billing_date" name="next_billing_date"
                                class="form-input <?= isset($errors['next_billing_date']) ? 'border-red-500' : '' ?>"
                                value="<?= htmlspecialchars($sub['next_billing_date'], ENT_QUOTES, 'UTF-8') ?>"
                                required>
                            <?php if (isset($errors['next_billing_date'])): ?>
                                <p class="form-error">
                                    <?= $errors['next_billing_date'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label for="end_date" class="form-label">End date <span
                                class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="date" id="end_date" name="end_date" class="form-input"
                            value="<?= htmlspecialchars($sub['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="auto_renews" value="1" class="w-4 h-4 rounded text-blue-600"
                            <?= ($sub['auto_renews'] ?? 1) ? 'checked' : '' ?>> Auto-renews
                    </label>
                </div>

                <!-- Reminder -->
                <div class="space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Reminder</h2>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="reminder_enabled" id="reminder_enabled" value="1"
                            class="w-4 h-4 rounded text-blue-600" <?= $reminder ? 'checked' : '' ?>
                        onchange="document.getElementById('reminder_days_wrap').classList.toggle('hidden',
                        !this.checked)">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Email reminder before
                            billing date</span>
                    </label>
                    <div id="reminder_days_wrap" class="<?= $reminder ? '' : 'hidden' ?> ml-7">
                        <label for="reminder_days" class="form-label">Days before</label>
                        <select id="reminder_days" name="reminder_days" class="form-select w-40">
                            <?php foreach ([1, 2, 3, 5, 7] as $d): ?>
                                <option value="<?= $d ?>" <?= ($reminder['days_before'] ?? 3) == $d ? 'selected' : '' ?>>
                                    <?= $d ?> day
                                    <?= $d > 1 ? 's' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-textarea"
                        rows="3"><?= htmlspecialchars($sub['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="submit" class="btn-primary">Save changes</button>
                    <a href="<?= Config::Get('APP_URL') ?>/dashboard/view-subscription.php?id=<?= $subId ?>"
                        class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>