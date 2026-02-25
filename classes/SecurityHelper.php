<?php
/**
 * SecurityHelper.php — Authentication guards, password handling, rate limiting
 */
class SecurityHelper
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function requireLogin(): void
    {
        if (!Session::IsLoggedIn()) {
            Session::Flash('error', 'Please log in to continue.');
            UrlHelper::redirect('auth/login.php');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (Session::Get('user_role') !== 'admin') {
            http_response_code(403);
            require_once dirname(__DIR__) . '/errors/403.php';
            exit;
        }
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function recordLoginAttempt(string $email): void
    {
        $key = 'login_attempts_' . md5($email);
        $attempts = Session::Get($key, ['count' => 0, 'first_at' => time()]);

        // Reset if cooldown period has passed
        if ((time() - $attempts['first_at']) > (self::LOCKOUT_MINUTES * 60)) {
            $attempts = ['count' => 0, 'first_at' => time()];
        }

        $attempts['count']++;
        Session::Put($key, $attempts);
    }

    public static function isLockedOut(string $email): bool
    {
        $key = 'login_attempts_' . md5($email);
        $attempts = Session::Get($key, ['count' => 0, 'first_at' => time()]);

        if ($attempts['count'] < self::MAX_LOGIN_ATTEMPTS) {
            return false;
        }

        $elapsed = time() - $attempts['first_at'];
        if ($elapsed > (self::LOCKOUT_MINUTES * 60)) {
            Session::Delete($key);
            return false;
        }

        return true;
    }

    public static function clearLoginAttempts(string $email): void
    {
        Session::Delete('login_attempts_' . md5($email));
    }

    public static function lockoutRemainingMinutes(string $email): int
    {
        $key = 'login_attempts_' . md5($email);
        $attempts = Session::Get($key, ['count' => 0, 'first_at' => time()]);
        $elapsed = time() - $attempts['first_at'];
        $remaining = (self::LOCKOUT_MINUTES * 60) - $elapsed;
        return max(0, (int) ceil($remaining / 60));
    }

    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /** Verify a user owns a resource (prevent IDOR attacks). */
    public static function ownsResource(int $resourceUserId): bool
    {
        $userId = Session::UserId();
        if ($userId === null) {
            return false;
        }
        // Admins can access any resource
        if (Session::Get('user_role') === 'admin') {
            return true;
        }
        return $userId === $resourceUserId;
    }

    public static function ownsResourceOrFail(int $resourceUserId): void
    {
        if (!self::ownsResource($resourceUserId)) {
            http_response_code(403);
            require_once dirname(__DIR__) . '/errors/403.php';
            exit;
        }
    }
}
