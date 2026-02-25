<?php
/**
 * CurrencyHelper.php — Currency formatting and conversion
 */
class CurrencyHelper
{
    private static array $symbols = [
        'GBP' => '£',
        'USD' => '$',
        'EUR' => '€',
        'CAD' => 'CA$',
        'AUD' => 'A$',
        'JPY' => '¥',
        'CHF' => 'CHF',
        'SEK' => 'kr',
        'NOK' => 'kr',
        'DKK' => 'kr',
        'PLN' => 'zł',
        'INR' => '₹',
        'BRL' => 'R$',
        'MXN' => 'MX$',
        'SGD' => 'S$',
        'HKD' => 'HK$',
        'NZD' => 'NZ$',
        'ZAR' => 'R',
    ];

    public static function getSymbol(string $currency): string
    {
        return self::$symbols[strtoupper($currency)] ?? strtoupper($currency) . ' ';
    }

    public static function formatGBP(float $amount): string
    {
        return '£' . number_format($amount, 2);
    }

    public static function format(float $amount, string $currency = 'GBP'): string
    {
        $symbol = self::getSymbol($currency);
        $formatted = number_format($amount, 2);

        // For currencies with text codes, add space
        if (strlen($symbol) > 2) {
            return $symbol . ' ' . $formatted;
        }

        return $symbol . $formatted;
    }

    /**
     * Convert an amount from one currency to GBP using stored rates.
     *
     * @param PDO $pdo
     */
    public static function convert(float $amount, string $fromCurrency, PDO $pdo): float
    {
        if (strtoupper($fromCurrency) === 'GBP') {
            return $amount;
        }

        $stmt = $pdo->prepare(
            'SELECT rate FROM exchange_rates WHERE from_currency = ? AND to_currency = ?'
        );
        $stmt->execute([strtoupper($fromCurrency), 'GBP']);
        $row = $stmt->fetch();

        if ($row) {
            return round($amount * $row['rate'], 2);
        }

        // Fallback: return as-is (no rate available)
        Logger::warning("No exchange rate found for {$fromCurrency} → GBP");
        return $amount;
    }

    public static function getSupportedCurrencies(): array
    {
        return array_keys(self::$symbols);
    }
}
