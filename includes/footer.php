<?php
/**
 * footer.php — Global site footer
 */
$isLoggedIn = Session::IsLoggedIn();
$baseUrl = Config::Get('APP_URL', 'http://localhost:8000');
?>
</main>
<?php if (!$isLoggedIn): ?>
    <!-- Public footer -->
    <footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 py-10 mt-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm">©
                <?= date('Y') ?> SubTrack. All rights reserved.
            </p>
            <div class="flex items-center gap-6 text-sm">
                <a href="<?= $baseUrl ?>/privacy-policy.php" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="<?= $baseUrl ?>/terms.php" class="hover:text-white transition-colors">Terms</a>
                <a href="<?= $baseUrl ?>/contact.php" class="hover:text-white transition-colors">Contact</a>
            </div>
        </div>
    </footer>
<?php else: ?>
    <!-- Dashboard footer -->
    <footer class="mt-8 pb-4 px-4 sm:px-6 lg:px-8">
        <p class="text-xs text-slate-400 dark:text-slate-600 text-center">
            SubTrack &copy;
            <?= date('Y') ?> &nbsp;·&nbsp;
            <a href="<?= $baseUrl ?>/privacy-policy.php" class="hover:text-slate-600 transition-colors">Privacy</a>
            &nbsp;·&nbsp;
            <a href="<?= $baseUrl ?>/terms.php" class="hover:text-slate-600 transition-colors">Terms</a>
        </p>
    </footer>
    </div><!-- end main content wrapper -->
<?php endif; ?>
</div><!-- close main or sidebar wrapper -->

<!-- ── Toast notification container ── -->
<div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="false"></div>

<!-- ── Delete confirmation modal ── -->
<div id="modal-delete" class="modal-backdrop hidden fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-delete')"></div>
    <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 animate-fade-in">
        <div class="flex items-center gap-3 mb-4">
            <div
                class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                <?= UIHelper::icon('trash', 'w-5 h-5 text-red-600 dark:text-red-400') ?>
            </div>
            <div>
                <h3 class="font-semibold text-slate-900 dark:text-white">Delete subscription?</h3>
                <p id="modal-delete-name" class="text-sm text-slate-500 dark:text-slate-400"></p>
            </div>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-6">This will permanently delete the subscription and all
            its payment history. This action cannot be undone.</p>
        <div class="flex gap-3 justify-end">
            <button onclick="closeModal('modal-delete')" class="btn-secondary">Cancel</button>
            <button id="modal-delete-confirm" class="btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- ── Global JavaScript ── -->
<script>
    (function () {
        'use strict';

        // ── Theme Toggle ──────────────────────────────────────────────────────
        function setTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('subtrack_theme', theme);

            // Persist to DB via AJAX
            fetch('<?= $baseUrl ?>/ajax/set_theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + encodeURIComponent(theme) + '&csrf_token=<?= Csrf::TokenValue() ?>'
            }).catch(() => { });
        }

        function toggleTheme() {
            const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            setTheme(current === 'dark' ? 'light' : 'dark');
        }

        // Bind theme toggles
        document.querySelectorAll('#theme-toggle, #theme-toggle-public').forEach(btn => {
            btn && btn.addEventListener('click', toggleTheme);
        });

        // ── Mobile sidebar ─────────────────────────────────────────────────────
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });
        }

        // ── Modal helpers ─────────────────────────────────────────────────────
        window.openModal = function (id) {
            const el = document.getElementById(id);
            if (el) { el.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
        };
        window.closeModal = function (id) {
            const el = document.getElementById(id);
            if (el) { el.classList.add('hidden'); document.body.style.overflow = ''; }
        };

        // Close modals on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach(m => {
                    m.classList.add('hidden');
                    document.body.style.overflow = '';
                });
            }
        });

        // ── Toast notifications ────────────────────────────────────────────────
        window.showToast = function (message, type = 'info', duration = 4000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const icons = {
                success: '<svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                error: '<svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
                info: '<svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>',
            };

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = (icons[type] || '') + `<span class="flex-1">${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slide-out-right 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        };

        // ── Delete subscription ────────────────────────────────────────────────
        window.confirmDelete = function (id, name) {
            const modal = document.getElementById('modal-delete');
            const nameEl = document.getElementById('modal-delete-name');
            const confirmBtn = document.getElementById('modal-delete-confirm');
            if (!modal || !confirmBtn) return;

            nameEl && (nameEl.textContent = name);
            openModal('modal-delete');

            confirmBtn.onclick = function () {
                const fd = new FormData();
                fd.append('id', id);
                fd.append('csrf_token', '<?= Csrf::TokenValue() ?>');

                fetch('<?= $baseUrl ?>/ajax/delete_subscription.php', {
                    method: 'POST', body: fd
                })
                    .then(r => r.json())
                    .then(data => {
                        closeModal('modal-delete');
                        if (data.success) {
                            const card = document.querySelector('[data-sub-id="' + id + '"]');
                            if (card) { card.style.opacity = '0'; card.style.transition = 'opacity 0.3s'; setTimeout(() => card.remove(), 300); }
                            showToast(data.message || 'Subscription deleted.', 'success');
                        } else {
                            showToast(data.message || 'Failed to delete.', 'error');
                        }
                    })
                    .catch(() => showToast('Network error. Please try again.', 'error'));
            };
        };

        // ── Pause / Resume subscription ────────────────────────────────────────
        function updateSubStatus(id, status) {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', status);
            fd.append('csrf_token', '<?= Csrf::TokenValue() ?>');
            fetch('<?= $baseUrl ?>/ajax/update_status.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast(data.message || 'Failed.', 'error');
                    }
                })
                .catch(() => showToast('Network error.', 'error'));
        }
        window.pauseSubscription = id => updateSubStatus(id, 'paused');
        window.resumeSubscription = id => updateSubStatus(id, 'active');

        // ── Calendar month navigation ─────────────────────────────────────────
        window.loadCalendarMonth = function (year, month) {
            const grid = document.getElementById('calendar-grid');
            if (!grid) return;

            grid.style.opacity = '0.4';
            fetch('<?= $baseUrl ?>/ajax/calendar_month.php?year=' + year + '&month=' + month, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.text())
                .then(html => {
                    grid.innerHTML = html;
                    grid.style.opacity = '1';
                    const url = new URL(window.location);
                    url.searchParams.set('year', year);
                    url.searchParams.set('month', month);
                    window.history.pushState({}, '', url);
                })
                .catch(() => { grid.style.opacity = '1'; });
        };

    })();
</script>

<!-- Show flash toasts -->
<?php if ($flashSuccess = Session::Flash('toast_success')): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast(<?= json_encode($flashSuccess) ?>, 'success'));</script>
<?php endif; ?>
<?php if ($flashError = Session::Flash('toast_error')): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast(<?= json_encode($flashError) ?>, 'error'));</script>
<?php endif; ?>

<!-- Cookie banner -->
<?php require_once dirname(__DIR__) . '/components/cookie_banner.php'; ?>

</body>

</html>