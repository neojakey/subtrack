<?php
/**
 * PaymentLogRepository.php — Historical log of past payments
 */
class PaymentLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $subscriptionId, int $userId, float $amount, string $currency, ?float $amountGbp, string $paidDate, ?string $notes = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO payment_log (subscription_id, user_id, amount, currency, amount_gbp, paid_date, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$subscriptionId, $userId, $amount, $currency, $amountGbp, $paidDate, $notes]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findByUser(int $userId, array $filters = [], int $page = 1, int $perPage = 30): array
    {
        $where = ['pl.user_id = ?'];
        $params = [$userId];

        if (!empty($filters['subscription_id'])) {
            $where[] = 'pl.subscription_id = ?';
            $params[] = (int) $filters['subscription_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 's.category_id = ?';
            $params[] = (int) $filters['category_id'];
        }
        if (!empty($filters['from_date'])) {
            $where[] = 'pl.paid_date >= ?';
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where[] = 'pl.paid_date <= ?';
            $params[] = $filters['to_date'];
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->pdo->prepare(
            "SELECT pl.*, s.name AS subscription_name, s.currency AS sub_currency,
                    c.name AS category_name, c.colour AS category_colour
             FROM payment_log pl
             JOIN subscriptions s ON s.id = pl.subscription_id
             JOIN categories c ON c.id = s.category_id
             WHERE {$whereClause}
             ORDER BY pl.paid_date DESC, pl.id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByUser(int $userId, array $filters = []): int
    {
        $where = ['pl.user_id = ?'];
        $params = [$userId];

        if (!empty($filters['subscription_id'])) {
            $where[] = 'pl.subscription_id = ?';
            $params[] = (int) $filters['subscription_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 's.category_id = ?';
            $params[] = (int) $filters['category_id'];
        }
        if (!empty($filters['from_date'])) {
            $where[] = 'pl.paid_date >= ?';
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where[] = 'pl.paid_date <= ?';
            $params[] = $filters['to_date'];
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM payment_log pl
             JOIN subscriptions s ON s.id = pl.subscription_id
             WHERE ' . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function findBySubscription(int $subscriptionId, int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM payment_log WHERE subscription_id = ? AND user_id = ? ORDER BY paid_date DESC'
        );
        $stmt->execute([$subscriptionId, $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Monthly totals for the last N months (for analytics chart).
     * Returns array of ['month' => 'YYYY-MM', 'total' => float]
     */
    public function monthlyTotals(int $userId, int $months = 12): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DATE_FORMAT(paid_date, '%Y-%m') AS month,
                    SUM(COALESCE(amount_gbp, amount)) AS total
             FROM payment_log
             WHERE user_id = ?
               AND paid_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(paid_date, '%Y-%m')
             ORDER BY month ASC"
        );
        $stmt->execute([$userId, $months]);
        return $stmt->fetchAll();
    }

    /**
     * Total spending per category (for analytics doughnut).
     */
    public function categoryTotals(int $userId, int $months = 12): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.name, c.colour, SUM(COALESCE(pl.amount_gbp, pl.amount)) AS total
             FROM payment_log pl
             JOIN subscriptions s ON s.id = pl.subscription_id
             JOIN categories c ON c.id = s.category_id
             WHERE pl.user_id = ?
               AND pl.paid_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY s.category_id, c.name, c.colour
             ORDER BY total DESC"
        );
        $stmt->execute([$userId, $months]);
        return $stmt->fetchAll();
    }
}
