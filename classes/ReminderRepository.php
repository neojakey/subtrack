<?php
/**
 * ReminderRepository.php — User-configured reminder rules per subscription
 */
class ReminderRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $subscriptionId, int $userId, int $daysBefore, bool $sendEmail = true): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reminders (subscription_id, user_id, days_before, send_email)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$subscriptionId, $userId, $daysBefore, (int) $sendEmail]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, s.name AS subscription_name, s.next_billing_date, s.amount, s.currency
             FROM reminders r
             JOIN subscriptions s ON s.id = r.subscription_id
             WHERE r.user_id = ?
             ORDER BY s.next_billing_date ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findBySubscription(int $subscriptionId, int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM reminders WHERE subscription_id = ? AND user_id = ?'
        );
        $stmt->execute([$subscriptionId, $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Find reminders due today (for cron script).
     * A reminder is due when: next_billing_date = TODAY + days_before
     */
    public function findDue(): array
    {
        $stmt = $this->pdo->query(
            "SELECT r.*, s.name AS subscription_name, s.next_billing_date, s.amount, s.currency,
                    u.email AS user_email, u.full_name AS user_name
             FROM reminders r
             JOIN subscriptions s ON s.id = r.subscription_id
             JOIN users u ON u.id = r.user_id
             WHERE r.is_active = 1
               AND r.send_email = 1
               AND s.status = 'active'
               AND DATE_ADD(CURDATE(), INTERVAL r.days_before DAY) = s.next_billing_date
               AND (r.last_sent_at IS NULL OR DATE(r.last_sent_at) < CURDATE())"
        );
        return $stmt->fetchAll();
    }

    public function markSent(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE reminders SET last_sent_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function update(int $id, int $userId, int $daysBefore, bool $sendEmail, bool $isActive): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reminders SET days_before = ?, send_email = ?, is_active = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$daysBefore, (int) $sendEmail, (int) $isActive, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM reminders WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function deleteBySubscription(int $subscriptionId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM reminders WHERE subscription_id = ? AND user_id = ?'
        );
        return $stmt->execute([$subscriptionId, $userId]);
    }
}
