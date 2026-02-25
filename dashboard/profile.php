<?php
require_once dirname(__DIR__) . '/config/config.php';
SecurityHelper::requireLogin();

$pdo = Database::getInstance();
$userId = Session::UserId();

$userRepo = new UserRepository($pdo);
$user = $userRepo->findById($userId);

$errors = [];
$success = '';

if (Input::isPost()) {
    Csrf::ValidateOrFail();

    $action = Input::raw('action', '');

    // ── Update profile ──────────────────────────────────────────────────────
    if ($action === 'update_profile') {
        $fullName = trim(Input::raw('full_name', ''));
        $theme = Input::raw('theme', 'light');

        if (strlen($fullName) < 2) {
            $errors['full_name'] = 'Name must be at least 2 characters.';
        } else {
            $userRepo->updateProfile($userId, $fullName, in_array($theme, ['light', 'dark']) ? $theme : 'light');
            Session::Put('user_name', $fullName);
            Session::Put('theme', $theme);
            Session::Flash('toast_success', 'Profile updated.');
            UrlHelper::redirect('dashboard/profile.php');
        }
    }

    // ── Change password ─────────────────────────────────────────────────────
    if ($action === 'change_password') {
        $current = Input::raw('current_password', '');
        $new = Input::raw('new_password', '');
        $confirm = Input::raw('confirm_password', '');

        if (empty($user['password_hash'])) {
            $errors['password'] = 'Your account uses Google Sign-In and has no password to change.';
        } elseif (!SecurityHelper::verifyPassword($current, $user['password_hash'])) {
            $errors['current_password'] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        } else {
            $userRepo->updatePassword($userId, SecurityHelper::hashPassword($new));
            Session::Flash('toast_success', 'Password changed successfully.');
            UrlHelper::redirect('dashboard/profile.php');
        }
    }

    // ── Delete account ──────────────────────────────────────────────────────
    if ($action === 'delete_account') {
        $confirmText = trim(Input::raw('confirm_delete', ''));
        if ($confirmText !== 'DELETE') {
            $errors['delete'] = 'Please type DELETE to confirm.';
        } else {
            // Delete all user data
            $userRepo->delete($userId);
            Session::Destroy();
            header('Location: ' . Config::Get('APP_URL') . '/auth/register.php?deleted=1');
            exit;
        }
    }
}

$pageTitle = 'Profile & settings — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto space-y-8">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Profile & settings</h1>

    <!-- ── Profile info ── -->
    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold text-slate-900 dark:text-white">Your profile</h2>
        </div>
        <div class="card-body">
            <form method="POST" class="space-y-4" novalidate>
                <?= Csrf::GetToken() ?>
                <input type="hidden" name="action" value="update_profile">

                <div class="flex items-center gap-4 mb-5">
                    <?php if (!empty($user['google_avatar_url'])): ?>
                        <img src="<?= htmlspecialchars($user['google_avatar_url'], ENT_QUOTES, 'UTF-8') ?>" alt=""
                            class="w-16 h-16 rounded-full">
                    <?php else: ?>
                        <div
                            class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold">
                            <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p class="text-sm text-slate-500">
                            <?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <?php if (!empty($user['google_id'])): ?>
                            <span class="badge bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 mt-1">Google
                                account</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="full_name" class="form-label">Full name</label>
                    <input type="text" id="full_name" name="full_name" class="form-input"
                        value="<?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    <?php if (isset($errors['full_name'])): ?>
                        <p class="form-error">
                            <?= $errors['full_name'] ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="form-label">Theme</label>
                    <div class="flex items-center gap-3">
                        <?php foreach (['light' => '☀️ Light', 'dark' => '🌙 Dark'] as $val => $lbl): ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="theme" value="<?= $val ?>" class="w-4 h-4 text-blue-600"
                                    <?= ($user['theme_preference'] ?? 'light') === $val ? 'checked' : '' ?>>
                                <span class="text-sm text-slate-700 dark:text-slate-300">
                                    <?= $lbl ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Save changes</button>
            </form>
        </div>
    </div>

    <!-- ── Change password ── -->
    <?php if (!empty($user['password_hash'])): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-slate-900 dark:text-white">Change password</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="space-y-4" novalidate>
                    <?= Csrf::GetToken() ?>
                    <input type="hidden" name="action" value="change_password">
                    <?php if (isset($errors['password'])): ?>
                        <?= UIHelper::Alert('error', $errors['password']) ?>
                    <?php endif; ?>
                    <div>
                        <label for="current_password" class="form-label">Current password</label>
                        <input type="password" id="current_password" name="current_password"
                            class="form-input <?= isset($errors['current_password']) ? 'border-red-500' : '' ?>" required>
                        <?php if (isset($errors['current_password'])): ?>
                            <p class="form-error">
                                <?= $errors['current_password'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="new_password" class="form-label">New password</label>
                        <input type="password" id="new_password" name="new_password"
                            class="form-input <?= isset($errors['new_password']) ? 'border-red-500' : '' ?>" required
                            placeholder="At least 8 characters">
                        <?php if (isset($errors['new_password'])): ?>
                            <p class="form-error">
                                <?= $errors['new_password'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="confirm_password" class="form-label">Confirm new password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="form-input <?= isset($errors['confirm_password']) ? 'border-red-500' : '' ?>" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <p class="form-error">
                                <?= $errors['confirm_password'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary">Change password</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- ── Export & Import ── -->
    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold text-slate-900 dark:text-white">Data export</h2>
        </div>
        <div class="card-body space-y-3">
            <p class="text-sm text-slate-600 dark:text-slate-400">Download your data as CSV files compatible with Excel,
                Google Sheets, and other tools.</p>
            <div class="flex flex-wrap gap-3">
                <a href="<?= Config::Get('APP_URL') ?>/dashboard/export.php?type=subscriptions" class="btn-secondary">
                    <?= UIHelper::icon('arrow-down-tray', 'w-4 h-4') ?> Export subscriptions
                </a>
                <a href="<?= Config::Get('APP_URL') ?>/dashboard/export.php?type=payments" class="btn-secondary">
                    <?= UIHelper::icon('arrow-down-tray', 'w-4 h-4') ?> Export payment history
                </a>
            </div>
        </div>
    </div>

    <!-- ── Danger zone ── -->
    <div class="card border-red-200 dark:border-red-900">
        <div class="card-header border-red-100 dark:border-red-900">
            <h2 class="font-semibold text-red-700 dark:text-red-400">Danger zone</h2>
        </div>
        <div class="card-body">
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                Deleting your account is permanent and cannot be undone. All subscriptions, payment history, and
                reminders will be erased.
            </p>
            <button onclick="openModal('modal-delete-account')" class="btn-danger">Delete my account</button>
        </div>
    </div>
</div>

<!-- Delete account modal -->
<div id="modal-delete-account" class="modal-backdrop hidden fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-delete-account')"></div>
    <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 animate-fade-in">
        <h3 class="text-lg font-bold text-red-700 dark:text-red-400 mb-3">Delete account permanently?</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-5">This will delete all your subscriptions, payment
            history, and account. This cannot be undone. Type <code
                class="text-red-600 font-mono font-bold">DELETE</code> to confirm.</p>
        <form method="POST" class="space-y-4">
            <?= Csrf::GetToken() ?>
            <input type="hidden" name="action" value="delete_account">
            <input type="text" name="confirm_delete" class="form-input border-red-300 dark:border-red-700 font-mono"
                placeholder="DELETE" autocomplete="off">
            <?php if (isset($errors['delete'])): ?>
                <p class="form-error">
                    <?= $errors['delete'] ?>
                </p>
            <?php endif; ?>
            <div class="flex gap-3">
                <button type="submit" class="btn-danger flex-1">Delete everything</button>
                <button type="button" onclick="closeModal('modal-delete-account')"
                    class="btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>