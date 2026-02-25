<?php
/**
 * CalendarService.php — Builds calendar event data for any month/year
 */
class CalendarService
{
    public function __construct(
        private SubscriptionRepository $subRepo
    ) {
    }

    /**
     * Build an array of events keyed by date ('YYYY-MM-DD').
     * Each value is an array of subscription events due on that day.
     *
     * For multi-month coverage (subscriptions that recur within the month),
     * we compute all billing dates that fall within the given month.
     */
    public function buildMonth(int $userId, int $year, int $month): array
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        // Get all active subscriptions (not just those with next_billing_date in this month)
        $allSubs = $this->subRepo->findAllByUser($userId, 'active', 0, '', '', 'next_billing_date', 'ASC', 1, 500);

        $events = [];

        foreach ($allSubs as $sub) {
            // Find all billing dates for this subscription that fall within this month
            $dates = $this->getBillingDatesInMonth($sub['next_billing_date'], $sub['billing_cycle'], $monthStart, $monthEnd, $sub['start_date']);
            foreach ($dates as $date) {
                $events[$date][] = $sub;
            }
        }

        return $events;
    }

    private function getBillingDatesInMonth(string $nextBillingDate, string $cycle, string $monthStart, string $monthEnd, string $startDate): array
    {
        $dates = [];

        // Walk backward to find the earliest occurrence at or before monthStart
        $cursor = $nextBillingDate;
        while ($cursor > $monthStart) {
            $prev = $this->prevBillingDate($cursor, $cycle);
            if ($prev < $startDate)
                break;
            $cursor = $prev;
        }

        // Walk forward collecting dates within the month
        $safetyLimit = 60; // prevent infinite loops
        $i = 0;
        while ($cursor <= $monthEnd && $i++ < $safetyLimit) {
            if ($cursor >= $monthStart) {
                $dates[] = $cursor;
            }
            $cursor = DateHelper::nextBillingDate($cursor, $cycle);
        }

        return $dates;
    }

    private function prevBillingDate(string $date, string $cycle): string
    {
        $d = new DateTime($date);
        switch ($cycle) {
            case 'weekly':
                $d->modify('-7 days');
                break;
            case 'monthly':
                $d->modify('-1 month');
                break;
            case 'quarterly':
                $d->modify('-3 months');
                break;
            case 'biannual':
                $d->modify('-6 months');
                break;
            case 'annual':
                $d->modify('-1 year');
                break;
        }
        return $d->format('Y-m-d');
    }
}
