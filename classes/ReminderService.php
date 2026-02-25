<?php
/**
 * ReminderService.php — Schedules and sends email reminders
 */
class ReminderService
{
    public function __construct(
        private ReminderRepository $repo
    ) {
    }

    /**
     * Find all reminders that are due today and send them.
     * Called by cron script daily.
     */
    public function sendDueReminders(): int
    {
        $due = $this->repo->findDue();
        $sent = 0;

        foreach ($due as $reminder) {
            try {
                $result = Mailer::sendReminder(
                    $reminder['user_email'],
                    $reminder['user_name'],
                    $reminder['subscription_name'],
                    (float) $reminder['amount'],
                    $reminder['currency'],
                    $reminder['next_billing_date']
                );
                if ($result) {
                    $this->repo->markSent($reminder['id']);
                    $sent++;
                    Logger::info("Reminder sent for subscription {$reminder['subscription_name']} to {$reminder['user_email']}");
                }
            } catch (Throwable $e) {
                Logger::error("Failed to send reminder ID {$reminder['id']}: " . $e->getMessage());
            }
        }

        return $sent;
    }
}
