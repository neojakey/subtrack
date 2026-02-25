<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();

$catRepo = new CategoryRepository($pdo);
$categories = $catRepo->findAll();

$errors = [];
$data = [
    'name' => '',
    'provider' => '',
    'logo_url' => '',
    'category_id' => '',
    'amount' => '',
    'currency' => 'GBP',
    'billing_cycle' => 'monthly',
    'billing_day' => '',
    'start_date' => date('Y-m-d'),
    'next_billing_date' => '',
    'end_date' => '',
    'auto_renews' => 1,
    'url' => '',
    'notes' => '',
    'reminder_enabled' => false,
    'reminder_days' => 3,
];

if (Input::isPost()) {
    Csrf::ValidateOrFail();

    $data['name'] = trim(Input::raw('name', ''));
    $data['provider'] = trim(Input::raw('provider', ''));
    $data['logo_url'] = trim(Input::raw('logo_url', ''));
    $data['category_id'] = (int) Input::raw('category_id', 0);
    $data['amount'] = Input::raw('amount', '');
    $data['currency'] = strtoupper(Input::raw('currency', 'GBP'));
    $data['billing_cycle'] = Input::raw('billing_cycle', 'monthly');
    $data['billing_day'] = Input::raw('billing_day', null);
    $data['start_date'] = Input::raw('start_date', date('Y-m-d'));
    $data['next_billing_date'] = Input::raw('next_billing_date', '');
    $data['end_date'] = Input::raw('end_date', '');
    $data['auto_renews'] = !empty($_POST['auto_renews']) ? 1 : 0;
    $data['url'] = trim(Input::raw('url', ''));
    $data['notes'] = trim(Input::raw('notes', ''));
    $data['reminder_enabled'] = !empty($_POST['reminder_enabled']);
    $data['reminder_days'] = (int) Input::raw('reminder_days', 3);

    // Validation
    if (empty($data['name']))
        $errors['name'] = 'Service name is required.';
    if ($data['category_id'] <= 0)
        $errors['category_id'] = 'Please select a category.';
    if (!is_numeric($data['amount']) || (float) $data['amount'] <= 0)
        $errors['amount'] = 'Please enter a valid amount.';

    // Auto-calculate next billing date if not provided
    if (empty($data['next_billing_date']) && !empty($data['start_date'])) {
        $data['next_billing_date'] = DateHelper::firstBillingDate($data['start_date'], $data['billing_cycle']);
    }
    if (empty($data['next_billing_date']))
        $errors['next_billing_date'] = 'Next billing date is required.';

    if (empty($errors)) {
        $subRepo = new SubscriptionRepository($pdo);
        $subId = $subRepo->create($userId, $data);

        // Create reminder if enabled
        if ($data['reminder_enabled'] && $data['reminder_days'] > 0) {
            $reminderRepo = new ReminderRepository($pdo);
            $reminderRepo->create($subId, $userId, $data['reminder_days']);
        }

        Session::Flash('toast_success', htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8') . ' added successfully!');
        UrlHelper::redirect("dashboard/view-subscription.php?id={$subId}");
    }
}

$pageTitle = 'Add subscription — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="<?= Config::Get('APP_URL') ?>/dashboard/subscriptions.php" class="btn-icon">
            <?= UIHelper::icon('chevron-left', 'w-5 h-5') ?>
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Add subscription</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" id="add-sub-form" class="space-y-6" novalidate>
                <?= Csrf::GetToken() ?>

                <!-- ── Step 1: Service name & logo ── -->
                <div class="form-step space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Service details</h2>

                    <div>
                        <label for="name" class="form-label">Service name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name"
                            class="form-input <?= isset($errors['name']) ? 'border-red-500' : '' ?>"
                            value="<?= htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="e.g. Netflix, Spotify, iCloud…" required>
                        <?php if (isset($errors['name'])): ?>
                            <p class="form-error">
                                <?= $errors['name'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="provider" class="form-label">Provider <span
                                class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="text" id="provider" name="provider" class="form-input"
                            value="<?= htmlspecialchars($data['provider'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="e.g. Netflix Inc.">
                    </div>

                    <div>
                        <label for="logo_url" class="form-label">Logo URL <span
                                class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="url" id="logo_url" name="logo_url" class="form-input"
                            value="<?= htmlspecialchars($data['logo_url'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="https://…">
                        <p class="form-hint">Leave blank to use the favicon from the website URL below.</p>
                    </div>
                </div>

                <!-- ── Step 2: Category ── -->
                <div class="form-step space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Category <span class="text-red-500">*</span></h2>
                    <?php if (isset($errors['category_id'])): ?>
                        <p class="form-error">
                            <?= $errors['category_id'] ?>
                        </p>
                    <?php endif; ?>

                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                        <?php foreach ($categories as $cat): ?>
                            <label
                                class="cat-option <?= (int) $data['category_id'] === (int) $cat['id'] ? 'cat-option-active' : '' ?>">
                                <input type="radio" name="category_id" value="<?= $cat['id'] ?>" class="sr-only"
                                    <?= (int) $data['category_id'] === (int) $cat['id'] ? 'checked' : '' ?>
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

                <!-- ── Step 3: Amount & Currency ── -->
                <div class="form-step space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Amount</h2>

                    <div class="flex gap-3">
                        <div class="w-28 flex-shrink-0">
                            <label for="currency" class="form-label">Currency</label>
                            <select id="currency" name="currency" class="form-select">
                                <?php foreach (CurrencyHelper::getSupportedCurrencies() as $cur): ?>
                                    <option value="<?= $cur ?>" <?= $data['currency'] === $cur ? 'selected' : '' ?>>
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
                                value="<?= htmlspecialchars($data['amount'], ENT_QUOTES, 'UTF-8') ?>" step="0.01"
                                min="0.01" placeholder="9.99" required>
                            <?php if (isset($errors['amount'])): ?>
                                <p class="form-error">
                                    <?= $errors['amount'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ── Step 4: Billing cycle ── -->
                <div class="form-step space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Billing cycle</h2>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $cycles = ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'biannual' => 'Every 6 months', 'annual' => 'Annual'];
                        foreach ($cycles as $val => $label):
                            ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="billing_cycle" value="<?= $val ?>" class="sr-only"
                                    <?= $data['billing_cycle'] === $val ? 'checked' : '' ?>
                                onchange="document.querySelectorAll('.cycle-pill').forEach(e=>e.classList.replace('pill-btn-active','pill-btn'));this.nextElementSibling.classList.replace('pill-btn','pill-btn-active')">
                                <span
                                    class="cycle-pill <?= $data['billing_cycle'] === $val ? 'pill-btn-active' : 'pill-btn' ?>">
                                    <?= $label ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ── Step 5: Dates ── -->
                <div class="form-step space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Dates</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="form-label">Start date</label>
                            <input type="date" id="start_date" name="start_date" class="form-input"
                                value="<?= htmlspecialchars($data['start_date'], ENT_QUOTES, 'UTF-8') ?>"
                                onchange="calcNextDate()">
                        </div>
                        <div>
                            <label for="next_billing_date" class="form-label">Next billing date <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="next_billing_date" name="next_billing_date"
                                class="form-input <?= isset($errors['next_billing_date']) ? 'border-red-500' : '' ?>"
                                value="<?= htmlspecialchars($data['next_billing_date'], ENT_QUOTES, 'UTF-8') ?>">
                            <p class="form-hint">Auto-calculated from start date. Override if needed.</p>
                            <?php if (isset($errors['next_billing_date'])): ?>
                                <p class="form-error">
                                    <?= $errors['next_billing_date'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label for="end_date" class="form-label">End date <span
                                class="text-slate-400 font-normal">(optional — leave blank for ongoing)</span></label>
                        <input type="date" id="end_date" name="end_date" class="form-input"
                            value="<?= htmlspecialchars($data['end_date'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 cursor-pointer">
                        <input type="checkbox" name="auto_renews" value="1" class="w-4 h-4 rounded text-blue-600"
                            <?= $data['auto_renews'] ? 'checked' : '' ?>>
                        Auto-renews
                    </label>
                </div>

                <!-- ── Step 6: Reminders ── -->
                <div class="form-step space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Reminder</h2>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="reminder_enabled" id="reminder_enabled" value="1"
                            class="w-4 h-4 rounded text-blue-600" <?= $data['reminder_enabled'] ? 'checked' : '' ?>
                        onchange="document.getElementById('reminder_days_wrap').classList.toggle('hidden',
                        !this.checked)">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Send email reminder before
                            billing date</span>
                    </label>

                    <div id="reminder_days_wrap" class="<?= $data['reminder_enabled'] ? '' : 'hidden' ?> ml-7">
                        <label for="reminder_days" class="form-label">Days before billing date</label>
                        <select id="reminder_days" name="reminder_days" class="form-select w-40">
                            <?php foreach ([1, 2, 3, 5, 7] as $d): ?>
                                <option value="<?= $d ?>" <?= $data['reminder_days'] == $d ? 'selected' : '' ?>>
                                    <?= $d ?> day
                                    <?= $d > 1 ? 's' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- ── Step 7: Notes & URL ── -->
                <div class="form-step space-y-4">
                    <h2
                        class="font-semibold text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-700">
                        Additional details</h2>

                    <div>
                        <label for="url" class="form-label">Manage subscription URL <span
                                class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="url" id="url" name="url" class="form-input"
                            value="<?= htmlspecialchars($data['url'], ENT_QUOTES, 'UTF-8') ?>" placeholder="https://…">
                    </div>

                    <div>
                        <label for="notes" class="form-label">Notes <span
                                class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea id="notes" name="notes" class="form-textarea" rows="3"
                            placeholder="Account name, payment method, etc."><?= htmlspecialchars($data['notes'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="submit" class="btn-primary">Save subscription</button>
                    <a href="<?= Config::Get('APP_URL') ?>/dashboard/subscriptions.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function calcNextDate() {
        const startDate = document.getElementById('start_date').value;
        const cycle = document.querySelector('[name="billing_cycle"]:checked')?.value || 'monthly';
        if (!startDate) return;

        // Simple JS approximation — PHP recalculates precisely on submit
        const d = new Date(startDate);
        switch (cycle) {
            case 'weekly': d.setDate(d.getDate() + 7); break;
            case 'monthly': d.setMonth(d.getMonth() + 1); break;
            case 'quarterly': d.setMonth(d.getMonth() + 3); break;
            case 'biannual': d.setMonth(d.getMonth() + 6); break;
            case 'annual': d.setFullYear(d.getFullYear() + 1); break;
        }
        const iso = d.toISOString().split('T')[0];
        const nextEl = document.getElementById('next_billing_date');
        if (!nextEl.value || nextEl.dataset.autoSet) {
            nextEl.value = iso;
            nextEl.dataset.autoSet = '1';
        }
    }

    document.querySelectorAll('[name="billing_cycle"]').forEach(r => r.addEventListener('change', calcNextDate));
    document.addEventListener('DOMContentLoaded', calcNextDate);
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>