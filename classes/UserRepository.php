<?php
/**
 * UserRepository.php — CRUD for users, Google account linking, preferences
 */
class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(string $email, string $passwordHash, string $fullName): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password_hash, full_name, auth_provider, email_verified, verification_token)
             VALUES (?, ?, ?, \'local\', 0, ?)'
        );
        $token = bin2hex(random_bytes(32));
        $stmt->execute([$email, $passwordHash, $fullName, $token]);
        return (int) $this->pdo->lastInsertId();
    }

    public function createGoogleUser(string $email, string $fullName, string $googleId, string $avatarUrl): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password_hash, full_name, google_id, google_avatar_url, auth_provider, email_verified)
             VALUES (?, NULL, ?, ?, ?, \'google\', 1)'
        );
        $stmt->execute([$email, $fullName, $googleId, $avatarUrl]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByGoogleId(string $googleId): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE google_id = ?');
        $stmt->execute([$googleId]);
        return $stmt->fetch();
    }

    public function findByVerificationToken(string $token): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE verification_token = ?');
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function linkGoogleAccount(int $userId, string $googleId, string $avatarUrl): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET google_id = ?, google_avatar_url = ?, auth_provider = \'both\' WHERE id = ?'
        );
        return $stmt->execute([$googleId, $avatarUrl, $userId]);
    }

    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function verifyEmail(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?'
        );
        return $stmt->execute([$userId]);
    }

    public function updatePassword(int $userId, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$passwordHash, $userId]);
    }

    public function updateProfile(int $userId, string $fullName, string $email, string $currency): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET full_name = ?, email = ?, currency = ? WHERE id = ?'
        );
        return $stmt->execute([$fullName, $email, $currency, $userId]);
    }

    public function updateTheme(int $userId, string $theme): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
        return $stmt->execute([$theme, $userId]);
    }

    public function setGdprConsent(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET gdpr_consent = 1, gdpr_consent_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$userId]);
    }

    public function delete(int $userId): bool
    {
        // CASCADE deletes subscriptions, payment_log, reminders
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    public function findAll(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare(
            'SELECT id, email, full_name, role, auth_provider, email_verified, last_login_at, created_at
             FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$perPage, $offset]);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function updateRole(int $userId, string $role): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $userId]);
    }

    public function createPasswordReset(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+48 hours'));
        // Invalidate any old tokens
        $this->pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$userId]);
        $stmt = $this->pdo->prepare(
            'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $token, $expires]);
        return $token;
    }

    public function findValidPasswordReset(string $token): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT pr.*, u.email FROM password_resets pr
             JOIN users u ON u.id = pr.user_id
             WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used_at IS NULL'
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function markPasswordResetUsed(string $token): bool
    {
        $stmt = $this->pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE token = ?");
        return $stmt->execute([$token]);
    }
}
