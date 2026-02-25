<?php
/**
 * CurrencyService.php — Fetches and caches exchange rates
 */
class CurrencyService
{
    public function __construct(
        private CurrencyRepository $repo
    ) {
    }

    /**
     * Refresh all exchange rates from exchangerate-api.com.
     * Free tier: https://v6.exchangerate-api.com/v6/{key}/latest/GBP
     */
    public function refreshRates(): bool
    {
        $apiKey = Config::Get('EXCHANGE_RATE_API_KEY');
        if (empty($apiKey)) {
            Logger::warning('CurrencyService: No EXCHANGE_RATE_API_KEY configured. Using fallback rates.');
            return $this->applyFallbackRates();
        }

        $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/GBP";
        $ctx = stream_context_create(['http' => ['timeout' => 10]]);
        $response = @file_get_contents($url, false, $ctx);

        if (!$response) {
            Logger::error('CurrencyService: Failed to fetch exchange rates from API.');
            return false;
        }

        $data = json_decode($response, true);
        if (!isset($data['result']) || $data['result'] !== 'success') {
            Logger::error('CurrencyService: API returned error: ' . ($data['error-type'] ?? 'unknown'));
            return false;
        }

        $count = 0;
        foreach ($data['conversion_rates'] as $currency => $rate) {
            if ($currency === 'GBP')
                continue;
            // rate here is how much of $currency equals 1 GBP
            // For our use (from → GBP), we need the inverse
            $toGbp = 1 / $rate;
            $this->repo->upsertRate($currency, 'GBP', $toGbp);
            $count++;
        }

        Logger::info("CurrencyService: Refreshed {$count} exchange rates.");
        return true;
    }

    private function applyFallbackRates(): bool
    {
        $defaults = [
            'USD' => 0.79,
            'EUR' => 0.86,
            'CAD' => 0.58,
            'AUD' => 0.51,
            'JPY' => 0.0053,
            'CHF' => 0.91,
            'SEK' => 0.074,
            'NOK' => 0.074,
            'DKK' => 0.115,
            'PLN' => 0.197,
            'INR' => 0.0095,
            'SGD' => 0.59,
        ];
        foreach ($defaults as $from => $rate) {
            $this->repo->upsertRate($from, 'GBP', $rate);
        }
        return true;
    }

    public function getRate(string $fromCurrency, string $toCurrency = 'GBP'): float
    {
        if (strtoupper($fromCurrency) === strtoupper($toCurrency))
            return 1.0;
        $rate = $this->repo->getRate($fromCurrency, $toCurrency);
        return $rate !== false ? $rate : 1.0;
    }
}
