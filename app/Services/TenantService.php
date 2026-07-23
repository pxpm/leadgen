<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\MagicLinkLogin;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Rules\IndustriesWithinPlanLimit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantService
{
    /**
     * Create a new tenant with admin user and subscription.
     *
     * @param  array<string, mixed>  $data
     */
    public function createTenant(array $data): Tenant
    {
        $this->validateIndustries($data);

        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'locale' => $data['locale'] ?? 'pt',
                'stripe_customer_id' => $data['stripe_customer_id'] ?? null,
                'branding_config' => $data['branding_config'] ?? [],
                'notification_config' => $data['notification_config'] ?? [],
                'active_services' => $data['active_services'] ?? [],
                'service_config' => $data['service_config'] ?? [],
                'qualification_overrides' => $data['qualification_overrides'] ?? [],
            ]);

            // Attach industries via pivot
            if (! empty($data['industries'])) {
                $tenant->industries()->sync((array) $data['industries']);
            }

            $password = $data['admin_password'] ?? Str::random(32);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => bcrypt($password),
                'is_super_admin' => false,
            ]);

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $data['plan_id'],
                'stripe_subscription_id' => $data['stripe_subscription_id'] ?? null,
                'stripe_price_id' => $data['stripe_price_id'] ?? null,
                'status' => $data['subscription_status'] ?? 'active',
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
            ]);

            if ($data['send_magic_link'] ?? true) {
                $this->sendMagicLinkForFirstLogin($user);
            }

            return $tenant->load('subscriptions.plan');
        });
    }

    /**
     * Check whether the tenant has an active or trialing subscription.
     */
    public function isServiceActive(Tenant $tenant): bool
    {
        return $tenant->isActive();
    }

    /**
     * Update a tenant's subscription (change plan, status, dates).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSubscription(Tenant $tenant, array $data): Subscription
    {
        return DB::transaction(function () use ($tenant, $data) {
            // If activating a new subscription, mark previous active ones as canceled
            if (($data['status'] ?? null) === 'active') {
                $tenant->subscriptions()
                    ->whereIn('status', ['active', 'trialing'])
                    ->update(['status' => 'canceled', 'ends_at' => now()]);
            }

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $data['plan_id'],
                'status' => $data['status'] ?? 'active',
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            return $subscription;
        });
    }

    /**
     * Validate industries against the selected plan's max_industries limit.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function validateIndustries(array $data): void
    {
        $industries = $data['industries'] ?? [];
        $planId = $data['plan_id'] ?? null;

        if (! $planId) {
            return;
        }

        $plan = Plan::find($planId);

        if (! $plan) {
            return;
        }

        $rule = new IndustriesWithinPlanLimit($plan);

        $validator = Validator::make(
            ['industries' => $industries],
            ['industries' => ['required', 'array', $rule]],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }

    /**
     * Send a magic link email to the admin user for first login.
     */
    public function sendMagicLinkForFirstLogin(User $user): void
    {
        $magicLinkService = app(MagicLinkService::class);
        $url = $magicLinkService->createForFirstLogin($user);

        Mail::to($user->email)
            ->send(new MagicLinkLogin(
                magicLinkUrl: $url,
                userName: $user->name,
            ));
    }
}
