<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Stripe\Subscription;

final readonly class CheckoutSessionService
{
    public function createSession(int $price): Session
    {
        $stripe = new StripeClient($_ENV["STRIPE_SECRET_KEY"]);

        return $stripe->checkout->sessions->create([
            'success_url' => 'https://localhost/stripe/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'https://localhost/stripe/cancel',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'donation',
                        ],
                        'unit_amount' => $price,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
        ]);
    }

    public function createSessionSubscriber(int $price): Session
    {
        $stripe = new StripeClient($_ENV["STRIPE_SECRET_KEY"]);

        return $stripe->checkout->sessions->create([
            'success_url' => 'https://localhost/stripe/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'https://localhost/stripe/cancel',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'donation',
                        ],
                        'unit_amount' => $price,
                        'recurring' => [
                            'interval' => 'month',
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
        ]);
    }

    public function getSubscription(?string $subscriptionId = null): ?Subscription
    {
        if ($subscriptionId === null) {
            return null;
        }

        $stripe = new StripeClient($_ENV["STRIPE_SECRET_KEY"]);

        return $stripe->subscriptions->retrieve($subscriptionId);
    }

    public function cancelSubscription(string $subscriptionId): Subscription
    {
        $stripe = new StripeClient($_ENV["STRIPE_SECRET_KEY"]);

        return $stripe->subscriptions->cancel($subscriptionId);
    }

    public function getSession(string $sessionId): Session
    {
        $stripe = new StripeClient($_ENV["STRIPE_SECRET_KEY"]);

        return $stripe->checkout->sessions->retrieve($sessionId);
    }
}
