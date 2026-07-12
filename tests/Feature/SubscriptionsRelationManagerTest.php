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

test('can change subscription plan (upgrade/downgrade)', function () {
    $this->actingAs($this->superAdmin);

    $newPlan = Plan::factory()->create(['is_active' => true]);
    $sub = Subscription::factory()->active()->create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::test(SubscriptionsRelationManager::class, [
        'ownerRecord' => $this->tenant,
    ])
        ->callTableAction('edit', $sub, data: [
            'plan_id' => $newPlan->id,
        ])
        ->assertHasNoTableActionErrors();

    expect($sub->fresh()->plan_id)->toBe($newPlan->id);
});

test('cannot create or delete subscriptions from manager', function () {
    $this->actingAs($this->superAdmin);

    $component = Livewire::test(SubscriptionsRelationManager::class, [
        'ownerRecord' => $this->tenant,
    ]);

    // No create button available
    expect(fn () => $component->callTableAction('create'))
        ->toThrow(Exception::class);

    // No delete button available
    expect(fn () => $component->callTableAction('delete', $this->subscription))
        ->toThrow(Exception::class);
});
