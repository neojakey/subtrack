<?php
/**
 * DateHelper.php — Billing date calculations and date formatting
 */
class DateHelper
{
    /**
     * Calculate the next billing date from a given date based on billing cycle.
     */
    public static function nextBillingDate(string $fromDate, string $cycle): string
    {
        $date = new DateTime($fromDate);

        switch ($cycle) {
            case 'weekly':
                $date->modify('+7 days');
                break;
            case 'monthly':
                $date = self::addMonthsSafe($date, 1);
                break;
            case 'quarterly':
                $date = self::addMonthsSafe($date, 3);
                break;
            case 'biannual':
                $date = self::addMonthsSafe($date, 6);
                break;
            case 'annual':
                $date = self::addMonthsSafe($date, 12);
                break;
            default:
                $date = self::addMonthsSafe($date, 1);
        }

        return $date->format('Y-m-d');
    }

    /**
     * Add months safely, preserving the day (clamped to last day of month if necessary).
     */
    private static function addMonthsSafe(DateTime $date, int $months): DateTime
    {
        $day = (int) $date->format('j');
        $date->modify("+{$months} months");
        // If the day rolled over (e.g., Jan 31 + 1 month → Mar 3 on short Februaries),
        // snap back to the last day of that month.
        if ((int) $date->format('j') < $day) {
            $date->modify('last day of last month');
        }
        return $date;
    }

    /**
     * Calculate next billing date from the START date for a new subscription.
     */
    public static function firstBillingDate(string $startDate, string $cycle): string
    {
        return self::nextBillingDate($startDate, $cycle);
    }

    /**
     * Days until a given date from today.
     */
    public static function daysUntil(string $date): int
    {
        $today = new DateTime('today');
        $target = new DateTime($date);
        $diff = $today->diff($target);
        return $diff->invert ? -$diff->days : $diff->days;
    }

    /**
     * Format a date in UK format (e.g. 18 Mar 2026).
     */
    public static function formatUK(string $date): string
    {
        $d = new DateTime($date);
        return $d->format('j M Y');
    }

    /**
     * Format a DateTime as a UK date string.
     */
    public static function formatUKFull(string $date): string
    {
        $d = new DateTime($date);
        return $d->format('l, j F Y');
    }

    /**
     * Human-readable billing cycle label.
     */
    public static function billingCycleLabel(string $cycle): string
    {
        return match ($cycle) {
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'biannual' => 'Every 6 months',
            'annual' => 'Annually',
            default => ucfirst($cycle),
        };
    }

    /**
     * Convert any billing amount to a monthly equivalent.
     */
    public static function monthlyEquivalent(float $amount, string $cycle): float
    {
        return match ($cycle) {
            'weekly' => round($amount * 52 / 12, 2),
            'monthly' => round($amount, 2),
            'quarterly' => round($amount / 3, 2),
            'biannual' => round($amount / 6, 2),
            'annual' => round($amount / 12, 2),
            default => round($amount, 2),
        };
    }

    /**
     * "Due in N days" or "Due today" or "Overdue by N days" label.
     */
    public static function dueLabel(string $date): string
    {
        $days = self::daysUntil($date);
        if ($days === 0)
            return 'Due today';
        if ($days === 1)
            return 'Due tomorrow';
        if ($days > 1)
            return "Due in {$days} days";
        return 'Overdue by ' . abs($days) . ' days';
    }

    /**
     * Returns true if the date is today or in the past.
     */
    public static function isPast(string $date): bool
    {
        return self::daysUntil($date) <= 0;
    }

    /**
     * Returns true if the date is within the next N days.
     */
    public static function isUpcoming(string $date, int $withinDays = 30): bool
    {
        $days = self::daysUntil($date);
        return $days >= 0 && $days <= $withinDays;
    }
}
