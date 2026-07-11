<?php

declare(strict_types=1);

use App\Filament\Resources\TenantResource\RelationManagers\SubscriptionsRelationManager;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['is_super_admin' => true]);
    $this->plan = Plan::factory()->create(['is_active' => true]);
    $this->tenant = Tenant::factory()->create();
    $this->subscription = Subscription::factory()->active()->create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
    ]);
});

test('lists subscriptions for a tenant', function () {
    $this->actingAs($this->superAdmin);

    Livewire::test(SubscriptionsRelationManager::class, [
        'ownerRecord' => $this->tenant,
    ])
        ->assertCanSeeTableRecords([$this->subscription]);
});

test('can create a manual subscription', function () {
    $this->actingAs($this->superAdmin);

    $plan = Plan::factory()->create(['is_active' => true]);

    Livewire::test(SubscriptionsRelationManager::class, [
        'ownerRecord' => $this->tenant,
    ])
        ->callTableAction('create', data: [
            'plan_id' => $plan->id,
            'status' => 'active',
        ])
        ->assertHasNoTableActionErrors();

    expect($this->tenant->subscriptions()->count())->toBe(2);
});

test('can edit subscription status', function () {
    $this->actingAs($this->superAdmin);

    $sub = Subscription::factory()->active()->create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::test(SubscriptionsRelationManager::class, [
        'ownerRecord' => $this->tenant,
    ])
        ->callTableAction('edit', $sub, data: [
            'plan_id' => $this->plan->id,
            'status' => 'canceled',
        ])
        ->assertHasNoTableActionErrors();

    expect($sub->fresh()->status->value)->toBe('canceled');
});

test('can delete a subscription', function () {
    $this->actingAs($this->superAdmin);

    $sub = Subscription::factory()->active()->create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::test(SubscriptionsRelationManager::class, [
        'ownerRecord' => $this->tenant,
    ])
        ->callTableAction('delete', $sub);

    expect(Subscription::find($sub->id))->toBeNull();
});
