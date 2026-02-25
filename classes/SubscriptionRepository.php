<?php
/**
 * SubscriptionRepository.php — Full CRUD for subscriptions
 */
class SubscriptionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO subscriptions
             (user_id, category_id, name, provider, logo_url, logo_path, amount, currency,
              billing_cycle, billing_day, billing_weekday, next_billing_date, start_date,
              end_date, status, auto_renews, notes, url)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'active\', ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['category_id'],
            $data['name'],
            $data['provider'] ?? null,
            $data['logo_url'] ?? null,
            $data['logo_path'] ?? null,
            $data['amount'],
            $data['currency'] ?? 'GBP',
            $data['billing_cycle'] ?? 'monthly',
            $data['billing_day'] ?? null,
            $data['billing_weekday'] ?? null,
            $data['next_billing_date'],
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['auto_renews'] ?? 1,
            $data['notes'] ?? null,
            $data['url'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id, int $userId): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.*, c.name AS category_name, c.slug AS category_slug,
                    c.icon AS category_icon, c.colour AS category_colour
             FROM subscriptions s
             JOIN categories c ON c.id = s.category_id
             WHERE s.id = ? AND s.user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function findAllByUser(
        int $userId,
        string $status = '',
        int $categoryId = 0,
        string $cycle = '',
        string $search = '',
        string $sort = 'next_billing_date',
        string $dir = 'ASC',
        int $page = 1,
        int $perPage = 20
    ): array {
        $allowed = ['next_billing_date', 'amount', 'name', 'created_at'];
        $sort = in_array($sort, $allowed, true) ? $sort : 'next_billing_date';
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $where = ['s.user_id = ?'];
        $params = [$userId];

        if ($status !== '') {
            $where[] = 's.status = ?';
            $params[] = $status;
        }
        if ($categoryId > 0) {
            $where[] = 's.category_id = ?';
            $params[] = $categoryId;
        }
        if ($cycle !== '') {
            $where[] = 's.billing_cycle = ?';
            $params[] = $cycle;
        }
        if ($search !== '') {
            $where[] = '(s.name LIKE ? OR s.provider LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->pdo->prepare(
            "SELECT s.*, c.name AS category_name, c.slug AS category_slug,
                    c.icon AS category_icon, c.colour AS category_colour
             FROM subscriptions s
             JOIN categories c ON c.id = s.category_id
             WHERE {$whereClause}
             ORDER BY s.{$sort} {$dir}
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByUser(
        int $userId,
        string $status = '',
        int $categoryId = 0,
        string $cycle = '',
        string $search = ''
    ): int {
        $where = ['user_id = ?'];
        $params = [$userId];

        if ($status !== '') {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($categoryId > 0) {
            $where[] = 'category_id = ?';
            $params[] = $categoryId;
        }
        if ($cycle !== '') {
            $where[] = 'billing_cycle = ?';
            $params[] = $cycle;
        }
        if ($search !== '') {
            $where[] = '(name LIKE ? OR provider LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $sql = 'SELECT COUNT(*) FROM subscriptions WHERE ' . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE subscriptions SET
             category_id = ?, name = ?, provider = ?, logo_url = ?, amount = ?,
             currency = ?, billing_cycle = ?, billing_day = ?, billing_weekday = ?,
             next_billing_date = ?, start_date = ?, end_date = ?,
             auto_renews = ?, notes = ?, url = ?
             WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['provider'] ?? null,
            $data['logo_url'] ?? null,
            $data['amount'],
            $data['currency'] ?? 'GBP',
            $data['billing_cycle'] ?? 'monthly',
            $data['billing_day'] ?? null,
            $data['billing_weekday'] ?? null,
            $data['next_billing_date'],
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['auto_renews'] ?? 1,
            $data['notes'] ?? null,
            $data['url'] ?? null,
            $id,
            $userId,
        ]);
    }

    public function updateStatus(int $id, int $userId, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE subscriptions SET status = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$status, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM subscriptions WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    /** Find upcoming active subscriptions in next N days */
    public function findUpcoming(int $userId, int $days = 30): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, c.name AS category_name, c.colour AS category_colour, c.icon AS category_icon
             FROM subscriptions s
             JOIN categories c ON c.id = s.category_id
             WHERE s.user_id = ?
               AND s.status = 'active'
               AND s.next_billing_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY s.next_billing_date ASC"
        );
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll();
    }

    /** Monthly total (all active subs, normalised to monthly) */
    public function monthlyTotal(int $userId): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT billing_cycle, amount FROM subscriptions WHERE user_id = ? AND status = 'active'"
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        $total = 0.0;
        foreach ($rows as $row) {
            $total += DateHelper::monthlyEquivalent((float) $row['amount'], $row['billing_cycle']);
        }
        return round($total, 2);
    }

    /** Active subscription count */
    public function activeCount(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND status = 'active'"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /** Next upcoming billing event */
    public function nextPayment(int $userId): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, c.name AS category_name, c.colour AS category_colour
             FROM subscriptions s
             JOIN categories c ON c.id = s.category_id
             WHERE s.user_id = ? AND s.status = 'active' AND s.next_billing_date >= CURDATE()
             ORDER BY s.next_billing_date ASC LIMIT 1"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /** Category spending summary */
    public function categorySummary(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.name, c.colour, c.icon,
                    SUM(CASE s.billing_cycle
                        WHEN 'weekly'    THEN s.amount * 52 / 12
                        WHEN 'quarterly' THEN s.amount / 3
                        WHEN 'biannual'  THEN s.amount / 6
                        WHEN 'annual'    THEN s.amount / 12
                        ELSE s.amount END) AS monthly_total
             FROM subscriptions s
             JOIN categories c ON c.id = s.category_id
             WHERE s.user_id = ? AND s.status = 'active'
             GROUP BY s.category_id, c.name, c.colour, c.icon
             ORDER BY monthly_total DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Advance the next_billing_date to the next period.
     * Called after a payment is logged.
     */
    public function advanceBillingDate(int $subscriptionId, int $userId): bool
    {
        $sub = $this->findById($subscriptionId, $userId);
        if (!$sub)
            return false;

        $next = DateHelper::nextBillingDate($sub['next_billing_date'], $sub['billing_cycle']);
        $stmt = $this->pdo->prepare(
            'UPDATE subscriptions SET next_billing_date = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$next, $subscriptionId, $userId]);
    }

    /** Find all subscriptions where next_billing_date has passed (for cron). */
    public function findOverdue(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM subscriptions WHERE status = 'active' AND next_billing_date < CURDATE()"
        );
        return $stmt->fetchAll();
    }

    /** All active subscriptions in a given month (for calendar). */
    public function findByMonth(int $userId, int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = sprintf('%04d-%02d-%02d', $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));

        $stmt = $this->pdo->prepare(
            "SELECT s.*, c.name AS category_name, c.colour AS category_colour, c.icon AS category_icon
             FROM subscriptions s
             JOIN categories c ON c.id = s.category_id
             WHERE s.user_id = ?
               AND s.status = 'active'
               AND s.next_billing_date BETWEEN ? AND ?
             ORDER BY s.next_billing_date ASC"
        );
        $stmt->execute([$userId, $start, $end]);
        return $stmt->fetchAll();
    }
}
