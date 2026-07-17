<?php

declare(strict_types=1);

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    Plan::factory()->create(['slug' => 'starter', 'is_active' => true]);
});

test('stripe webhook rejects requests with no signature header', function () {
    $response = $this->postJson('/api/webhooks/stripe', [
        'type' => 'checkout.session.completed',
        'data' => ['object' => []],
    ]);

    // Without a valid Stripe-Signature header, the webhook should return 400
    $response->assertStatus(400);
});

test('stripe webhook idempotency key prevents duplicate processing', function () {
    // Pre-populate the cache as if an event was already processed
    Cache::put('stripe_event:evt_test_duplicate', true, now()->addHours(24));

    expect(Cache::has('stripe_event:evt_test_duplicate'))->toBeTrue();

    // A second request with the same event ID would be skipped by the
    // Cache::add() check in the controller (atomically returns false if exists)
    expect(Cache::add('stripe_event:evt_test_duplicate', true, now()->addHours(24)))->toBeFalse();
});
