<?php
require_once dirname(__DIR__) . '/config/config.php';

if (Session::IsLoggedIn())
    UrlHelper::redirect('dashboard/');

$success = false;
$error = '';

if (Input::isPost()) {
    Csrf::ValidateOrFail();
    $email = strtolower(trim(Input::raw('email', '')));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pdo = Database::getInstance();
        $userRepo = new UserRepository($pdo);
        $user = $userRepo->findByEmail($email);

        if ($user) {
            $token = $userRepo->createPasswordReset($user['id']);
            Mailer::sendPasswordReset($email, $user['full_name'], $token);
            Logger::info("Password reset requested for: {$email}");
        }
        // Always show success (don't reveal whether email exists)
        $success = true;
    }
}

$pageTitle = 'Reset password — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Reset your password</h1>
            <p class="text-slate-500 dark:text-slate-400">Enter your email and we'll send a reset link.</p>
        </div>
        <div class="card">
            <div class="card-body space-y-4">
                <?php if ($success): ?>
                    <?= UIHelper::Alert('success', 'If an account exists with that email, you\'ll receive a reset link shortly. Check your inbox (and spam folder).') ?>
                    <a href="<?= Config::Get('APP_URL') ?>/auth/login.php" class="btn-secondary w-full justify-center">Back
                        to login</a>
                <?php else: ?>
                    <?php if ($error): ?>
                        <?= UIHelper::Alert('error', $error) ?>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <?= Csrf::GetToken() ?>
                        <div class="mb-4">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" id="email" name="email" class="form-input" required
                                placeholder="you@example.com">
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center">Send reset link</button>
                    </form>
                    <p class="text-center text-sm text-slate-500">
                        <a href="<?= Config::Get('APP_URL') ?>/auth/login.php" class="text-blue-600 hover:underline">Back to
                            login</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>