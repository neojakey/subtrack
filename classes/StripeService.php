<?php
/**
 * StripeService.php — STUB for future Pro tier
 *
 * SubTrack is currently free to use. This class exists as a scaffold for
 * a future Pro subscription tier (e.g. unlimited subscriptions).
 *
 * To activate: composer require stripe/stripe-php and implement TODO sections.
 */
class StripeService
{
    // TODO: Add STRIPE_SECRET_KEY and STRIPE_PUBLISHABLE_KEY to .env

    public function __construct()
    {
        // TODO: \Stripe\Stripe::setApiKey(Config::Get('STRIPE_SECRET_KEY'));
    }

    /**
     * Create a Stripe Checkout session for the Pro subscription.
     * TODO: Implement with Stripe Checkout API.
     */
    public function createCheckoutSession(int $userId, string $planId): string
    {
        // TODO: Return Stripe Checkout URL
        throw new RuntimeException('Stripe integration is not yet active. SubTrack is currently free.');
    }

    /**
     * Handle Stripe webhook events.
     * TODO: Implement customer.subscription.created, updated, deleted events.
     */
    public function handleWebhook(string $payload, string $signature): void
    {
        // TODO: \Stripe\Webhook::constructEvent($payload, $signature, Config::Get('STRIPE_WEBHOOK_SECRET'));
        throw new RuntimeException('Stripe webhook handling not yet implemented.');
    }

    /**
     * Cancel the Pro subscription for a user.
     * TODO: Implement subscription cancellation.
     */
    public function cancelSubscription(int $userId): bool
    {
        // TODO: Fetch Stripe customer ID from users table and cancel subscription
        throw new RuntimeException('Stripe integration is not yet active.');
    }

    /**
     * Check if a user has an active Pro subscription.
     * TODO: Check against Stripe or a local subscriptions/plans table.
     */
    public function isPro(int $userId): bool
    {
        // TODO: Query local pro_subscriptions table or Stripe API
        return false; // Everyone is on the free tier for now
    }
}
