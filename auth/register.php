<?php
require_once dirname(__DIR__) . '/config/config.php';

if (Session::IsLoggedIn()) {
    UrlHelper::redirect('dashboard/');
}

$errors = [];
$fields = ['full_name' => '', 'email' => ''];

if (Input::isPost()) {
    Csrf::ValidateOrFail();

    $fullName = trim(Input::raw('full_name', ''));
    $email    = strtolower(trim(Input::raw('email', '')));
    $password = Input::raw('password', '');
    $passConf = Input::raw('password_confirm', '');
    $gdpr     = !empty($_POST['gdpr_consent']);

    $fields['full_name'] = $fullName;
    $fields['email']     = $email;

    // Validation
    if (empty($fullName) || strlen($fullName) < 2) $errors['full_name'] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $passConf) $errors['password_confirm'] = 'Passwords do not match.';
    if (!$gdpr) $errors['gdpr'] = 'You must agree to the Privacy Policy to create an account.';

    if (empty($errors)) {
        $pdo      = Database::getInstance();
        $userRepo = new UserRepository($pdo);

        if ($userRepo->findByEmail($email)) {
            $errors['email'] = 'An account with this email already exists. <a href="' . Config::Get('APP_URL') . '/auth/login.php" class="underline">Log in?</a>';
        } else {
            $hash   = SecurityHelper::hashPassword($password);
            $userId = $userRepo->create($email, $hash, $fullName);
            $userRepo->setGdprConsent($userId);

            // Fetch user back for the token
            $user = $userRepo->findById($userId);
            if ($user && !empty($user['verification_token'])) {
                Mailer::sendVerification($email, $fullName, $user['verification_token']);
            }

            // Log them in
            session_regenerate_id(true);
            Session::Put('user_id', $userId);
            Session::Put('user_name', $fullName);
            Session::Put('user_email', $email);
            Session::Put('user_role', 'user');
            Session::Put('user_avatar', '');
            Session::Put('theme', 'light');

            Session::Flash('toast_success', 'Welcome to SubTrack! Check your email to verify your account.');
            UrlHelper::redirect('dashboard/');
        }
    }
}

$pageTitle = 'Create account — SubTrack';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="font-bold text-2xl text-slate-900 dark:text-white">SubTrack</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Create your account</h1>
            <p class="text-slate-500 dark:text-slate-400">Track every subscription. Never get surprised.</p>
        </div>

        <div class="card">
            <div class="card-body space-y-5">

                <!-- Google Sign-In -->
                <?= UIHelper::GoogleSignInButton('Sign up with Google') ?>

                <!-- Divider -->
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
                    <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">or sign up with email</span>
                    <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
                </div>

                <!-- Form -->
                <form method="POST" action="" novalidate class="space-y-4">
                    <?= Csrf::GetToken() ?>

                    <div>
                        <label for="full_name" class="form-label">Full name</label>
                        <input type="text" id="full_name" name="full_name" class="form-input <?= isset($errors['full_name']) ? 'border-red-500' : '' ?>"
                               value="<?= htmlspecialchars($fields['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                               autocomplete="name" required placeholder="Jane Smith">
                        <?php if (isset($errors['full_name'])): ?>
                        <p class="form-error"><?= $errors['full_name'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" id="email" name="email" class="form-input <?= isset($errors['email']) ? 'border-red-500' : '' ?>"
                               value="<?= htmlspecialchars($fields['email'], ENT_QUOTES, 'UTF-8') ?>"
                               autocomplete="email" required placeholder="you@example.com">
                        <?php if (isset($errors['email'])): ?>
                        <p class="form-error"><?= $errors['email'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input <?= isset($errors['password']) ? 'border-red-500' : '' ?>"
                               autocomplete="new-password" required placeholder="At least 8 characters">
                        <?php if (isset($errors['password'])): ?>
                        <p class="form-error"><?= $errors['password'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="password_confirm" class="form-label">Confirm password</label>
                        <input type="password" id="password_confirm" name="password_confirm"
                               class="form-input <?= isset($errors['password_confirm']) ? 'border-red-500' : '' ?>"
                               autocomplete="new-password" required placeholder="Repeat password">
                        <?php if (isset($errors['password_confirm'])): ?>
                        <p class="form-error"><?= $errors['password_confirm'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- GDPR consent — never pre-ticked -->
                    <div class="bg-slate-50 dark:bg-slate-900/40 rounded-xl p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="gdpr_consent" id="gdpr_consent" class="mt-0.5 w-4 h-4 rounded text-blue-600 focus:ring-blue-500"
                                   <?= !empty($_POST) && empty($fields['gdpr']) ? '' : '' ?>>
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                I agree to SubTrack's <a href="<?= Config::Get('APP_URL') ?>/privacy-policy.php" target="_blank" class="text-blue-600 hover:underline">Privacy Policy</a>
                                and <a href="<?= Config::Get('APP_URL') ?>/terms.php" target="_blank" class="text-blue-600 hover:underline">Terms of Service</a>,
                                and consent to my data being stored and processed to provide the subscription tracking service.
                            </span>
                        </label>
                        <?php if (isset($errors['gdpr'])): ?>
                        <p class="form-error mt-2"><?= $errors['gdpr'] ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center">Create account</button>
                </form>

                <p class="text-center text-sm text-slate-500 dark:text-slate-400">
                    Already have an account?
                    <a href="<?= Config::Get('APP_URL') ?>/auth/login.php" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
