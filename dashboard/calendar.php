<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();

$year = (int) Input::get('year', date('Y'));
$month = (int) Input::get('month', date('n'));

// Clamp valid ranges
$year = max(2000, min(2099, $year));
$month = max(1, min(12, $month));

$calService = new CalendarService(new SubscriptionRepository($pdo));
$eventsByDay = $calService->buildMonth($userId, $year, $month);

$pageTitle = 'Calendar — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';

// Build event JSON for modal popup
$eventsJson = json_encode($eventsByDay, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Calendar</h1>
    <a href="<?= Config::Get('APP_URL') ?>/dashboard/add-subscription.php" class="btn-primary">
        <?= UIHelper::icon('plus', 'w-4 h-4') ?> Add subscription
    </a>
</div>

<!-- Calendar grid -->
<div id="calendar-grid">
    <?= UIHelper::CalendarGrid($eventsByDay, $year, $month) ?>
</div>

<!-- Mobile: list view (shown on small screens) -->
<div class="block lg:hidden mt-6">
    <h2 class="font-semibold text-slate-900 dark:text-white mb-3 text-lg">Upcoming this month</h2>
    <?php if (empty($eventsByDay)): ?>
        <p class="text-slate-500 text-sm">No payments scheduled this month.</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php
            ksort($eventsByDay);
            foreach ($eventsByDay as $date => $events):
                $daysUntil = DateHelper::daysUntil($date);
                if ($daysUntil < -1)
                    continue; // skip past dates except yesterday
                ?>
                <div class="card px-4 py-3">
                    <p class="text-xs font-semibold text-slate-400 mb-2">
                        <?= DateHelper::formatUK($date) ?> ·
                        <?= DateHelper::dueLabel($date) ?>
                    </p>
                    <?php foreach ($events as $ev): ?>
                        <div class="flex items-center justify-between py-1.5">
                            <div class="flex items-center gap-2">
                                <?= UIHelper::SubscriptionLogo($ev, 6) ?>
                                <span class="text-sm font-medium text-slate-900 dark:text-white">
                                    <?= htmlspecialchars($ev['name'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <span class="text-sm font-semibold">
                                <?= CurrencyHelper::format((float) $ev['amount'], $ev['currency'] ?? 'GBP') ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Day detail modal -->
<div id="modal-calendar-day" class="modal-backdrop hidden fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-calendar-day')"></div>
    <div
        class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[80vh] flex flex-col overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
            <h2 id="modal-cal-title" class="text-lg font-semibold text-slate-900 dark:text-white">Payments</h2>
            <button onclick="closeModal('modal-calendar-day')" class="btn-icon">
                <?= UIHelper::icon('x-mark', 'w-5 h-5') ?>
            </button>
        </div>
        <div id="modal-cal-body" class="flex-1 overflow-y-auto px-6 py-4 space-y-3"></div>
    </div>
</div>

<script>
    var calendarEvents = <?= $eventsJson ?>;

    window.showCalendarModal = function (dateKey) {
        var events = calendarEvents[dateKey] || [];
        var d = new Date(dateKey);
        var options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        document.getElementById('modal-cal-title').textContent = d.toLocaleDateString('en-GB', options);

        var html = '';
        if (events.length === 0) {
            html = '<p class="text-slate-500 text-sm text-center py-8">No payments on this date.</p>';
        } else {
            events.forEach(function (ev) {
                var amount = ev.amount ? parseFloat(ev.amount).toFixed(2) : '0.00';
                var currency = ev.currency || 'GBP';
                var symbols = { 'GBP': '£', 'USD': '$', 'EUR': '€' };
                var sym = symbols[currency] || currency + ' ';
                html += '<a href="<?= Config::Get('APP_URL') ?>/dashboard/view-subscription.php?id=' + ev.id + '" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group">' +
                    '<div>' +
                    '<p class="font-semibold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">' + ev.name + '</p>' +
                    '<p class="text-sm text-slate-500">' + (ev.category_name || '') + '</p>' +
                    '</div>' +
                    '<span class="font-bold text-slate-900 dark:text-white">' + sym + amount + '</span>' +
                    '</a>';
            });
        }
        document.getElementById('modal-cal-body').innerHTML = html;
        openModal('modal-calendar-day');
    };
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>