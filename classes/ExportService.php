<?php
/**
 * ExportService.php — CSV export of subscriptions and payment history
 */
class ExportService
{
    public function __construct(
        private SubscriptionRepository $subRepo,
        private PaymentLogRepository $paymentRepo
    ) {
    }

    /**
     * Stream subscriptions as CSV to browser.
     */
    public function streamSubscriptionsCsv(int $userId): void
    {
        $filename = 'subtrack-subscriptions-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        // BOM for Excel compatibility
        fputs($out, "\xEF\xBB\xBF");

        // Headers
        fputcsv($out, [
            'Name',
            'Provider',
            'Category',
            'Amount',
            'Currency',
            'Billing Cycle',
            'Next Billing Date',
            'Start Date',
            'Status',
            'Auto Renews',
            'URL',
            'Notes',
            'Created At',
        ]);

        $subs = $this->subRepo->findAllByUser($userId, '', 0, '', '', 'name', 'ASC', 1, 9999);
        foreach ($subs as $sub) {
            fputcsv($out, [
                $sub['name'],
                $sub['provider'] ?? '',
                $sub['category_name'] ?? '',
                number_format((float) $sub['amount'], 2),
                $sub['currency'],
                $sub['billing_cycle'],
                $sub['next_billing_date'],
                $sub['start_date'],
                $sub['status'],
                $sub['auto_renews'] ? 'Yes' : 'No',
                $sub['url'] ?? '',
                $sub['notes'] ?? '',
                $sub['created_at'],
            ]);
        }

        fclose($out);
    }

    /**
     * Stream payment history as CSV to browser.
     */
    public function streamPaymentHistoryCsv(int $userId): void
    {
        $filename = 'subtrack-payments-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Subscription',
            'Category',
            'Amount',
            'Currency',
            'Amount (GBP)',
            'Paid Date',
            'Notes',
        ]);

        $payments = $this->paymentRepo->findByUser($userId, [], 1, 9999);
        foreach ($payments as $p) {
            fputcsv($out, [
                $p['subscription_name'],
                $p['category_name'] ?? '',
                number_format((float) $p['amount'], 2),
                $p['currency'],
                $p['amount_gbp'] ? number_format((float) $p['amount_gbp'], 2) : '',
                $p['paid_date'],
                $p['notes'] ?? '',
            ]);
        }

        fclose($out);
    }

    /**
     * Parse a CSV import file and return an array of rows.
     */
    public function parseImportCsv(string $filePath): array
    {
        $rows = [];
        if (!file_exists($filePath))
            return $rows;

        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle); // skip header row
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 4) {
                $rows[] = array_combine(
                    array_map('strtolower', array_map('trim', $headers ?? [])),
                    $row
                );
            }
        }
        fclose($handle);
        return $rows;
    }
}
