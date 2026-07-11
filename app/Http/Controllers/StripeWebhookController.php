<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed');

            return response('', 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object()),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object()),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object()),
            default => Log::info('Unhandled Stripe event', ['type' => $event->type]),
        };

        return response()->noContent();
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $defaultPlan = Plan::where('slug', 'starter')->first();

        app(TenantService::class)->createTenant([
            'name' => $session->metadata->company_name ?? 'New Company',
            'slug' => $session->metadata->company_slug ?? Str::random(12),
            'locale' => 'pt',
            'industry_id' => 1,
            'admin_name' => $session->customer_details->name ?? 'Admin',
            'admin_email' => $session->customer_details->email,
            'plan_id' => $defaultPlan?->id ?? 1,
            'subscription_status' => 'active',
            'stripe_customer_id' => $session->customer,
            'stripe_subscription_id' => $session->subscription,
            'stripe_price_id' => $session->metadata->price_id ?? '',
            'send_magic_link' => true,
        ]);

        Log::info('Tenant created from Stripe checkout');
    }

    private function handleSubscriptionUpdated(object $subscription): void
    {
        Subscription::where('stripe_subscription_id', $subscription->id)
            ->update(['status' => $subscription->status]);
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        Subscription::where('stripe_subscription_id', $subscription->id)
            ->update(['status' => 'canceled', 'ends_at' => now()]);
    }
}
