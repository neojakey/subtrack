<?php
/**
 * AnalyticsService.php — Aggregates spend data for charts
 */
class AnalyticsService
{
    public function __construct(
        private PaymentLogRepository $paymentRepo,
        private SubscriptionRepository $subRepo
    ) {
    }

    /**
     * Monthly spend for the last 12 months.
     * Returns array of ['month' => 'YYYY-MM', 'total' => float, 'label' => 'Jan 2026']
     */
    public function monthlySpend(int $userId, int $months = 12): array
    {
        $rows = $this->paymentRepo->monthlyTotals($userId, $months);
        $keyed = [];
        foreach ($rows as $row) {
            $keyed[$row['month']] = (float) $row['total'];
        }

        // Fill in all months (even those with zero spend)
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $dt = new DateTime("first day of -{$i} months");
            $key = $dt->format('Y-m');
            $label = $dt->format('M Y');
            $result[] = [
                'month' => $key,
                'label' => $label,
                'total' => $keyed[$key] ?? 0.0,
            ];
        }
        return $result;
    }

    /**
     * Category breakdown for doughnut chart.
     * Returns array of ['name' => string, 'colour' => string, 'total' => float]
     */
    public function categoryBreakdown(int $userId): array
    {
        return $this->paymentRepo->categoryTotals($userId, 12);
    }

    /**
     * Spending by billing cycle — all normalised to monthly equivalent.
     */
    public function cycleBreakdown(int $userId): array
    {
        $subs = $this->subRepo->findAllByUser($userId, 'active', 0, '', '', 'name', 'ASC', 1, 500);

        $cycles = ['weekly' => 0, 'monthly' => 0, 'quarterly' => 0, 'biannual' => 0, 'annual' => 0];
        foreach ($subs as $sub) {
            $cycle = $sub['billing_cycle'] ?? 'monthly';
            $monthly = DateHelper::monthlyEquivalent((float) $sub['amount'], $cycle);
            $cycles[$cycle] = round($cycles[$cycle] + $monthly, 2);
        }

        $labels = [
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'biannual' => 'Every 6 months',
            'annual' => 'Annual',
        ];

        $result = [];
        foreach ($cycles as $cycle => $total) {
            $result[] = ['cycle' => $cycle, 'label' => $labels[$cycle], 'total' => $total];
        }
        return $result;
    }

    /**
     * Key stats for dashboard widgets.
     */
    public function dashboardStats(int $userId): array
    {
        $monthlyTotal = $this->subRepo->monthlyTotal($userId);
        return [
            'monthly_total' => $monthlyTotal,
            'annual_total' => round($monthlyTotal * 12, 2),
            'active_count' => $this->subRepo->activeCount($userId),
            'next_payment' => $this->subRepo->nextPayment($userId),
        ];
    }
}
