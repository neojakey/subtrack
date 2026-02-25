<?php
/**
 * CurrencyRepository.php — Stored exchange rates
 */
class CurrencyRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function upsertRate(string $fromCurrency, string $toCurrency, float $rate): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO exchange_rates (from_currency, to_currency, rate, fetched_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE rate = VALUES(rate), fetched_at = NOW()'
        );
        return $stmt->execute([strtoupper($fromCurrency), strtoupper($toCurrency), $rate]);
    }

    public function getRate(string $fromCurrency, string $toCurrency = 'GBP'): float|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT rate FROM exchange_rates WHERE from_currency = ? AND to_currency = ?'
        );
        $stmt->execute([strtoupper($fromCurrency), strtoupper($toCurrency)]);
        $row = $stmt->fetch();
        return $row ? (float) $row['rate'] : false;
    }

    public function getAllRates(string $toCurrency = 'GBP'): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT from_currency, rate, fetched_at FROM exchange_rates WHERE to_currency = ?'
        );
        $stmt->execute([strtoupper($toCurrency)]);
        return $stmt->fetchAll();
    }

    public function isStale(int $maxAgeHours = 24): bool
    {
        $stmt = $this->pdo->query(
            "SELECT MIN(fetched_at) FROM exchange_rates"
        );
        $oldest = $stmt->fetchColumn();
        if (!$oldest)
            return true;
        return strtotime($oldest) < time() - ($maxAgeHours * 3600);
    }
}
