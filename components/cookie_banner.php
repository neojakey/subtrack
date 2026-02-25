<?php
/**
 * cookie_banner.php — GDPR cookie consent banner
 */
$baseUrl = Config::Get('APP_URL', 'http://localhost:8000');
?>
<div id="cookie-banner" class="fixed bottom-0 inset-x-0 z-50 p-4 hidden" role="dialog" aria-label="Cookie consent"
    aria-live="polite">
    <div
        class="max-w-3xl mx-auto bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1">
                <p class="font-semibold text-slate-900 dark:text-white text-sm mb-1">🍪 We use cookies</p>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    We use strictly necessary cookies to keep you logged in, plus optional analytics cookies to improve
                    the app.
                    No advertising cookies, ever. Read our <a href="<?= $baseUrl ?>/privacy-policy.php"
                        class="underline text-blue-600 dark:text-blue-400 hover:no-underline">Privacy Policy</a>.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button id="cookie-accept-necessary" class="btn-secondary text-xs px-3 py-2">Necessary only</button>
                <button id="cookie-accept-all" class="btn-primary text-xs px-3 py-2">Accept all</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var consent = localStorage.getItem('subtrack_cookie_consent');
        if (!consent) {
            document.getElementById('cookie-banner').classList.remove('hidden');
        }

        function setCookieConsent(level) {
            localStorage.setItem('subtrack_cookie_consent', level);
            document.getElementById('cookie-banner').classList.add('hidden');
        }

        var btnAll = document.getElementById('cookie-accept-all');
        var btnNec = document.getElementById('cookie-accept-necessary');

        if (btnAll) btnAll.addEventListener('click', function () { setCookieConsent('all'); });
        if (btnNec) btnNec.addEventListener('click', function () { setCookieConsent('necessary'); });
    })();
</script>