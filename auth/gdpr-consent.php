<?php
require_once dirname(__DIR__) . '/config/config.php';

// Only reachable from google-callback.php redirect
$pending = Session::Get('pending_google_user');
if (!$pending || Session::IsLoggedIn()) {
    UrlHelper::redirect('auth/login.php');
}

$error = '';

if (Input::isPost()) {
    Csrf::ValidateOrFail();
    $gdpr = !empty($_POST['gdpr_consent']);

    if (!$gdpr) {
        $error = 'You must agree to the Privacy Policy and Terms to create an account.';
    } else {
        $pdo = Database::getInstance();
        $userRepo = new UserRepository($pdo);

        $userId = $userRepo->create($pending['email'], null, $pending['full_name'], true /* is_google */);
        $userRepo->linkGoogleAccount($userId, $pending['google_id'], $pending['avatar_url']);
        $userRepo->setGdprConsent($userId);
        $userRepo->markEmailVerified($userId);

        // Clear pending data from session
        Session::Delete('pending_google_user');

        session_regenerate_id(true);
        Session::Put('user_id', $userId);
        Session::Put('user_name', $pending['full_name']);
        Session::Put('user_email', $pending['email']);
        Session::Put('user_role', 'user');
        Session::Put('user_avatar', $pending['avatar_url']);
        Session::Put('theme', 'light');

        Mailer::sendWelcomeGoogle($pending['email'], $pending['full_name']);

        Session::Flash('toast_success', 'Welcome to SubTrack, ' . $pending['full_name'] . '!');
        UrlHelper::redirect('dashboard/');
    }
}

$pageTitle = 'Create your account — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="<?= htmlspecialchars($pending['avatar_url'], ENT_QUOTES, 'UTF-8') ?>" alt=""
                class="w-16 h-16 rounded-full mx-auto mb-4">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Almost there,
                <?= htmlspecialchars(explode(' ', $pending['full_name'])[0], ENT_QUOTES, 'UTF-8') ?>!
            </h1>
            <p class="text-slate-500 dark:text-slate-400">To create your SubTrack account we need your consent.</p>
        </div>
        <div class="card">
            <div class="card-body space-y-5">
                <?php if ($error): ?>
                    <?= UIHelper::Alert('error', $error) ?>
                <?php endif; ?>
                <form method="POST" novalidate class="space-y-4">
                    <?= Csrf::GetToken() ?>
                    <div class="bg-slate-50 dark:bg-slate-900/40 rounded-xl p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="gdpr_consent" id="gdpr_consent"
                                class="mt-0.5 w-4 h-4 rounded text-blue-600">
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                I agree to SubTrack's <a href="<?= Config::Get('APP_URL') ?>/privacy-policy.php"
                                    target="_blank" class="text-blue-600 underline">Privacy Policy</a>
                                and <a href="<?= Config::Get('APP_URL') ?>/terms.php" target="_blank"
                                    class="text-blue-600 underline">Terms of Service</a>,
                                and consent to my data being stored and processed to provide the subscription tracking
                                service.
                            </span>
                        </label>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">Create my account</button>
                </form>
                <p class="text-center text-sm text-slate-500">
                    Changed your mind?
                    <a href="<?= Config::Get('APP_URL') ?>/auth/login.php" class="text-blue-600 hover:underline">Go
                        back</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>