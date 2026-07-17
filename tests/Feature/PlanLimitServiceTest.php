<?php

declare(strict_types=1);

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\UsageLog;
use App\Services\PlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->planLimitService = app(PlanLimitService::class);
    $this->plan = Plan::factory()->create([
        'limits' => [
            'sms_monthly' => 100,
            'email_monthly' => 500,
            'ai_ingestion_monthly' => 50,
        ],
        'is_active' => true,
    ]);

    $this->tenant = Tenant::factory()->create();
    Subscription::factory()->active()->create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
    ]);
    $this->tenant->load('subscriptions.plan');
});

test('canSendSms returns true when under limit', function () {
    UsageLog::create([
        'tenant_id' => $this->tenant->id,
        'type' => 'sms_monthly',
        'period' => now()->format('Y-m'),
        'count' => 50,
    ]);

    expect($this->planLimitService->canSendSms($this->tenant))->toBeTrue();
});

test('canSendSms returns false when at limit', function () {
    UsageLog::create([
        'tenant_id' => $this->tenant->id,
        'type' => 'sms_monthly',
        'period' => now()->format('Y-m'),
        'count' => 100,
    ]);

    expect($this->planLimitService->canSendSms($this->tenant))->toBeFalse();
});

test('canSendSms returns false when limit is 0', function () {
    $plan = Plan::factory()->create([
        'limits' => ['sms_monthly' => 0, 'email_monthly' => 500, 'ai_ingestion_monthly' => 50],
        'is_active' => true,
    ]);
    $tenant = Tenant::factory()->create();
    Subscription::factory()->active()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
    ]);
    $tenant->load('subscriptions.plan');

    expect($this->planLimitService->canSendSms($tenant))->toBeFalse();
});

test('canSendEmail returns true when under limit', function () {
    UsageLog::create([
        'tenant_id' => $this->tenant->id,
        'type' => 'email_monthly',
        'period' => now()->format('Y-m'),
        'count' => 499,
    ]);

    expect($this->planLimitService->canSendEmail($this->tenant))->toBeTrue();
});

test('canIngestAi returns false when at limit', function () {
    UsageLog::create([
        'tenant_id' => $this->tenant->id,
        'type' => 'ai_ingestion_monthly',
        'period' => now()->format('Y-m'),
        'count' => 50,
    ]);

    expect($this->planLimitService->canIngestAi($this->tenant))->toBeFalse();
});

test('getUsage returns correct count for current month', function () {
    UsageLog::create([
        'tenant_id' => $this->tenant->id,
        'type' => 'sms_monthly',
        'period' => now()->format('Y-m'),
        'count' => 42,
    ]);

    expect($this->planLimitService->getUsage($this->tenant, 'sms_monthly'))->toBe(42);
});

test('getUsage returns 0 when no records exist', function () {
    expect($this->planLimitService->getUsage($this->tenant, 'sms_monthly'))->toBe(0);
});

test('getLimit returns correct limit from plan', function () {
    expect($this->planLimitService->getLimit($this->tenant, 'sms_monthly'))->toBe(100);
    expect($this->planLimitService->getLimit($this->tenant, 'email_monthly'))->toBe(500);
    expect($this->planLimitService->getLimit($this->tenant, 'ai_ingestion_monthly'))->toBe(50);
});

test('recordUsage increments correctly', function () {
    $this->planLimitService->recordUsage($this->tenant, 'sms_monthly');
    $this->planLimitService->recordUsage($this->tenant, 'sms_monthly');
    $this->planLimitService->recordUsage($this->tenant, 'sms_monthly');

    expect($this->planLimitService->getUsage($this->tenant, 'sms_monthly'))->toBe(3);
});

// ─── TOCTOU protection ────────────────────────────────────────

test('tryUse atomically checks and increments preventing race', function () {
    // Set usage to 99 — one away from limit of 100
    UsageLog::upsert(
        ['tenant_id' => $this->tenant->id, 'type' => 'sms_monthly', 'period' => now()->format('Y-m'), 'count' => 99],
        ['tenant_id', 'type', 'period'],
        ['count' => 99]
    );

    // First call should succeed (99 < 100)
    $first = $this->planLimitService->trySendSms($this->tenant);
    expect($first)->toBeTrue();

    // Second call should fail (100 >= 100) — atomic check prevents overshoot
    $second = $this->planLimitService->trySendSms($this->tenant);
    expect($second)->toBeFalse();

    // Usage should be exactly 100, not 101
    expect($this->planLimitService->getUsage($this->tenant, 'sms_monthly'))->toBe(100);
});

test('tryUse returns false when limit is zero', function () {
    $plan = Plan::factory()->create([
        'limits' => ['sms_monthly' => 0, 'email_monthly' => 500, 'ai_ingestion_monthly' => 50],
        'is_active' => true,
    ]);
    $tenant = Tenant::factory()->create();
    Subscription::factory()->active()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
    ]);
    $tenant->load('subscriptions.plan');

    expect($this->planLimitService->trySendSms($tenant))->toBeFalse();
    expect($this->planLimitService->getUsage($tenant, 'sms_monthly'))->toBe(0);
});

test('usage from previous month does not affect current', function () {
    UsageLog::create([
        'tenant_id' => $this->tenant->id,
        'type' => 'sms_monthly',
        'period' => now()->subMonth()->format('Y-m'),
        'count' => 100,
    ]);

    expect($this->planLimitService->getUsage($this->tenant, 'sms_monthly'))->toBe(0);
    expect($this->planLimitService->canSendSms($this->tenant))->toBeTrue();
});

test('throws if no active subscription', function () {
    $tenant = Tenant::factory()->create();

    expect(fn () => $this->planLimitService->canSendSms($tenant))
        ->toThrow(RuntimeException::class);
});
