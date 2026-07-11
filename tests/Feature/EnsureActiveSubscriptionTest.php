<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureActiveSubscription;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->plan = Plan::factory()->create(['is_active' => true]);
    $this->tenant = Tenant::factory()->create();
    $this->subscription = Subscription::factory()->active()->create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
    ]);
    $this->tenant->load('subscriptions.plan');
});

test('super admin bypasses subscription check', function () {
    $superAdmin = User::factory()->create([
        'is_super_admin' => true,
        'tenant_id' => null,
    ]);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $superAdmin);

    $middleware = app(EnsureActiveSubscription::class);
    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getStatusCode())->toBe(200);
});

test('user with active subscription passes', function () {
    $user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_super_admin' => false,
    ]);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureActiveSubscription::class);
    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getStatusCode())->toBe(200);
});

test('user with canceled subscription gets 402 for API', function () {
    $this->subscription->update(['status' => 'canceled']);

    $user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_super_admin' => false,
    ]);

    $request = Request::create('/api/widget/test/config', 'GET');
    $request->headers->set('Accept', 'application/json');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureActiveSubscription::class);
    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getStatusCode())->toBe(402);
});

test('user with canceled subscription gets redirected for web', function () {
    $this->subscription->update(['status' => 'canceled']);

    $user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_super_admin' => false,
    ]);

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureActiveSubscription::class);
    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->isRedirect())->toBeTrue();
});

test('tenant isActive returns correct values', function () {
    expect($this->tenant->isActive())->toBeTrue();

    $this->subscription->update(['status' => 'canceled']);
    expect($this->tenant->fresh()->isActive())->toBeFalse();

    $this->subscription->update(['status' => 'past_due']);
    expect($this->tenant->fresh()->isActive())->toBeFalse();
});

test('user with no tenant passes through', function () {
    $user = User::factory()->create([
        'tenant_id' => null,
        'is_super_admin' => false,
    ]);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureActiveSubscription::class);
    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getStatusCode())->toBe(200);
});
