<?php

declare(strict_types=1);

use App\Models\Industry;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenantService = app(TenantService::class);
    $this->plan = Plan::factory()->create(['slug' => 'starter', 'is_active' => true]);
    $this->industry = Industry::factory()->create();
});

test('createTenant creates tenant, user, and subscription in a transaction', function () {
    $tenant = $this->tenantService->createTenant([
        'name' => 'Test Company',
        'slug' => 'test-company',
        'locale' => 'pt',
        'industries' => [$this->industry->id],
        'admin_name' => 'Test Admin',
        'admin_email' => 'admin@testcompany.pt',
        'plan_id' => $this->plan->id,
        'send_magic_link' => false,
    ]);

    expect($tenant)->not->toBeNull();
    expect($tenant->name)->toBe('Test Company');
    expect($tenant->slug)->toBe('test-company');

    // User was created
    $user = $tenant->users()->first();
    expect($user)->not->toBeNull();
    expect($user->email)->toBe('admin@testcompany.pt');
    expect($user->is_super_admin)->toBeFalse();

    // Subscription was created
    $subscription = $tenant->subscriptions()->first();
    expect($subscription)->not->toBeNull();
    expect($subscription->plan_id)->toBe($this->plan->id);
    expect($subscription->status->value)->toBe('active');
});

test('createTenant rolls back on duplicate slug', function () {
    $this->tenantService->createTenant([
        'name' => 'First',
        'slug' => 'same-slug',
        'industries' => [$this->industry->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@first.pt',
        'plan_id' => $this->plan->id,
        'send_magic_link' => false,
    ]);

    expect(fn () => $this->tenantService->createTenant([
        'name' => 'Second',
        'slug' => 'same-slug',
        'industries' => [$this->industry->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@second.pt',
        'plan_id' => $this->plan->id,
        'send_magic_link' => false,
    ]))->toThrow(QueryException::class);

    expect(Tenant::count())->toBe(1);
});

test('createTenant auto-generates password when not provided', function () {
    $tenant = $this->tenantService->createTenant([
        'name' => 'Test',
        'slug' => 'test-slug',
        'industries' => [$this->industry->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@test.pt',
        'plan_id' => $this->plan->id,
        'send_magic_link' => false,
    ]);

    $user = $tenant->users()->first();
    expect($user->password)->not->toBeEmpty();
});

test('isServiceActive returns true for active subscription', function () {
    $tenant = $this->tenantService->createTenant([
        'name' => 'Active Co',
        'slug' => 'active-co',
        'industries' => [$this->industry->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@active.pt',
        'plan_id' => $this->plan->id,
        'subscription_status' => 'active',
        'send_magic_link' => false,
    ]);

    expect($this->tenantService->isServiceActive($tenant))->toBeTrue();
});

test('isServiceActive returns false for canceled subscription', function () {
    $tenant = $this->tenantService->createTenant([
        'name' => 'Canceled Co',
        'slug' => 'canceled-co',
        'industries' => [$this->industry->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@canceled.pt',
        'plan_id' => $this->plan->id,
        'subscription_status' => 'canceled',
        'send_magic_link' => false,
    ]);

    expect($this->tenantService->isServiceActive($tenant))->toBeFalse();
});

test('plan_id is required', function () {
    expect(fn () => $this->tenantService->createTenant([
        'name' => 'Test',
        'slug' => 'no-plan',
        'industries' => [$this->industry->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@test.pt',
        'plan_id' => 999,
        'send_magic_link' => false,
    ]))->toThrow(QueryException::class);
});

test('createTenant rejects industries exceeding plan limit', function () {
    $industry2 = Industry::factory()->create(['slug' => 'plumbing', 'name' => 'Plumbing']);

    // Starter plan allows max 1 industry
    $plan = Plan::factory()->create([
        'slug' => 'starter-limited',
        'is_active' => true,
        'limits' => ['sms_monthly' => 30, 'max_industries' => 1],
    ]);

    expect(fn () => $this->tenantService->createTenant([
        'name' => 'Test',
        'slug' => 'test-overlimit',
        'industries' => [$this->industry->id, $industry2->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@test.pt',
        'plan_id' => $plan->id,
        'send_magic_link' => false,
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('createTenant allows multiple industries on Pro plan', function () {
    $industry2 = Industry::factory()->create(['slug' => 'plumbing', 'name' => 'Plumbing']);

    // Pro plan allows max 2 industries
    $plan = Plan::factory()->create([
        'slug' => 'pro',
        'is_active' => true,
        'limits' => ['sms_monthly' => 100, 'max_industries' => 2],
    ]);

    $tenant = $this->tenantService->createTenant([
        'name' => 'Multi Industry Co',
        'slug' => 'multi-industry-co',
        'industries' => [$this->industry->id, $industry2->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@multi.pt',
        'plan_id' => $plan->id,
        'send_magic_link' => false,
    ]);

    expect($tenant->industries)->toHaveCount(2);
});

test('createTenant allows unlimited industries on Enterprise plan', function () {
    $industry2 = Industry::factory()->create(['slug' => 'plumbing', 'name' => 'Plumbing']);
    $industry3 = Industry::factory()->create(['slug' => 'hvac', 'name' => 'HVAC']);

    // Enterprise with no max_industries = unlimited
    $plan = Plan::factory()->create([
        'slug' => 'enterprise',
        'is_active' => true,
        'limits' => ['sms_monthly' => 300],
    ]);

    $tenant = $this->tenantService->createTenant([
        'name' => 'Enterprise Co',
        'slug' => 'enterprise-co',
        'industries' => [$this->industry->id, $industry2->id, $industry3->id],
        'admin_name' => 'Admin',
        'admin_email' => 'admin@enterprise.pt',
        'plan_id' => $plan->id,
        'send_magic_link' => false,
    ]);

    expect($tenant->industries)->toHaveCount(3);
});
