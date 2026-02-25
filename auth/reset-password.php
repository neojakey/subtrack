<?php
require_once dirname(__DIR__) . '/config/config.php';

if (Session::IsLoggedIn())
    UrlHelper::redirect('dashboard/');

$token = Input::rawGet('token', '');
$errors = [];
$success = false;
$validReset = false;
$resetRecord = null;

if (empty($token)) {
    UrlHelper::redirect('auth/forgot-password.php');
}

$pdo = Database::getInstance();
$userRepo = new UserRepository($pdo);
$resetRecord = $userRepo->findValidPasswordReset($token);

if (!$resetRecord) {
    $errors['token'] = 'This reset link is invalid or has expired. Please request a new one.';
} else {
    $validReset = true;
}

if ($validReset && Input::isPost()) {
    Csrf::ValidateOrFail();
    $password = Input::raw('password', '');
    $confirm = Input::raw('password_confirm', '');

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    } else {
        $userRepo->updatePassword((int) $resetRecord['user_id'], SecurityHelper::hashPassword($password));
        $userRepo->markPasswordResetUsed($token);
        Logger::info("Password reset completed for user ID: {$resetRecord['user_id']}");
        $success = true;
    }
}

$pageTitle = 'Set new password — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Set new password</h1>
        </div>
        <div class="card">
            <div class="card-body space-y-4">
                <?php if (isset($errors['token'])): ?>
                    <?= UIHelper::Alert('error', $errors['token']) ?>
                    <a href="<?= Config::Get('APP_URL') ?>/auth/forgot-password.php"
                        class="btn-primary w-full justify-center">Request new link</a>
                <?php elseif ($success): ?>
                    <?= UIHelper::Alert('success', 'Your password has been updated. You can now log in.') ?>
                    <a href="<?= Config::Get('APP_URL') ?>/auth/login.php" class="btn-primary w-full justify-center">Log
                        in</a>
                <?php else: ?>
                    <form method="POST" novalidate class="space-y-4">
                        <?= Csrf::GetToken() ?>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                        <div>
                            <label for="password" class="form-label">New password</label>
                            <input type="password" id="password" name="password"
                                class="form-input <?= isset($errors['password']) ? 'border-red-500' : '' ?>" required
                                placeholder="At least 8 characters">
                            <?php if (isset($errors['password'])): ?>
                                <p class="form-error">
                                    <?= $errors['password'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="password_confirm" class="form-label">Confirm new password</label>
                            <input type="password" id="password_confirm" name="password_confirm"
                                class="form-input <?= isset($errors['password_confirm']) ? 'border-red-500' : '' ?>"
                                required>
                            <?php if (isset($errors['password_confirm'])): ?>
                                <p class="form-error">
                                    <?= $errors['password_confirm'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center">Update password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>