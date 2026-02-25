<?php
require_once dirname(__DIR__) . '/config/config.php';

SecurityHelper::requireLogin() && false; // redirect if already logged in

if (Session::IsLoggedIn()) {
    UrlHelper::redirect('dashboard/');
}

$error = '';

if (Input::isPost()) {
    Csrf::ValidateOrFail();

    $email = Input::raw('email', '');
    $password = Input::raw('password', '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } elseif (SecurityHelper::isLockedOut($email)) {
        $mins = SecurityHelper::lockoutRemainingMinutes($email);
        $error = "Too many failed attempts. Please try again in {$mins} minute(s).";
    } else {
        $pdo = Database::getInstance();
        $userRepo = new UserRepository($pdo);
        $user = $userRepo->findByEmail($email);

        if ($user && $user['password_hash'] && SecurityHelper::verifyPassword($password, $user['password_hash'])) {
            SecurityHelper::clearLoginAttempts($email);
            session_regenerate_id(true);
            Session::Put('user_id', $user['id']);
            Session::Put('user_name', $user['full_name']);
            Session::Put('user_email', $user['email']);
            Session::Put('user_role', $user['role']);
            Session::Put('user_avatar', $user['google_avatar_url'] ?? '');
            Session::Put('theme', $user['theme_preference'] ?? 'light');
            $userRepo->updateLastLogin($user['id']);
            Session::Flash('toast_success', 'Welcome back, ' . $user['full_name'] . '!');
            UrlHelper::redirect('dashboard/');
        } else {
            SecurityHelper::recordLoginAttempt($email);
            $error = 'Invalid email or password.';
            Logger::info("Failed login attempt for: {$email}");
        }
    }
}

$pageTitle = 'Log in — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-6">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="font-bold text-2xl text-slate-900 dark:text-white">SubTrack</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Welcome back</h1>
            <p class="text-slate-500 dark:text-slate-400">Log in to your account</p>
        </div>

        <div class="card">
            <div class="card-body space-y-5">

                <!-- Google sign-in -->
                <?= UIHelper::GoogleSignInButton('Continue with Google') ?>

                <!-- Divider -->
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
                    <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">or continue with email</span>
                    <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
                </div>

                <!-- Error -->
                <?php if ($error): ?>
                    <?= UIHelper::Alert('error', $error) ?>
                <?php endif; ?>

                <!-- Login form -->
                <form method="POST" action="" novalidate class="space-y-4">
                    <?= Csrf::GetToken() ?>

                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" id="email" name="email" class="form-input" autocomplete="email"
                            value="<?= htmlspecialchars(Input::raw('email', ''), ENT_QUOTES, 'UTF-8') ?>" required
                            placeholder="you@example.com">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="form-label mb-0">Password</label>
                            <a href="<?= Config::Get('APP_URL') ?>/auth/forgot-password.php"
                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Forgot password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="form-input"
                            autocomplete="current-password" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center">Log in</button>
                </form>

                <p class="text-center text-sm text-slate-500 dark:text-slate-400">
                    No account yet?
                    <a href="<?= Config::Get('APP_URL') ?>/auth/register.php"
                        class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Create one</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>