<?php

declare(strict_types=1);

use App\Filament\Resources\Plans\Pages\CreatePlan;
use App\Filament\Resources\Plans\Pages\EditPlan;
use App\Filament\Resources\Plans\Pages\ListPlans;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['is_super_admin' => true]);
    $this->tenantUser = User::factory()->create(['is_super_admin' => false]);
});

test('super admin can view plans list', function () {
    $this->actingAs($this->superAdmin);

    Livewire::test(ListPlans::class)
        ->assertSuccessful();
});

test('non super admin cannot view plans list', function () {
    $this->actingAs($this->tenantUser);

    Livewire::test(ListPlans::class)
        ->assertForbidden();
});

test('super admin can create a plan', function () {
    $this->actingAs($this->superAdmin);

    Livewire::test(CreatePlan::class)
        ->fillForm([
            'name' => 'Custom Plan',
            'slug' => 'custom-plan',
            'limits' => [
                'sms_monthly' => 200,
                'email_monthly' => 1000,
                'ai_ingestion_monthly' => 100,
            ],
            'is_public' => false,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Plan::where('slug', 'custom-plan')->exists())->toBeTrue();
});

test('non super admin cannot create a plan', function () {
    $this->actingAs($this->tenantUser);

    Livewire::test(CreatePlan::class)
        ->assertForbidden();
});

test('super admin can edit a plan', function () {
    $this->actingAs($this->superAdmin);

    $plan = Plan::factory()->create();

    Livewire::test(EditPlan::class, ['record' => $plan->id])
        ->fillForm([
            'name' => 'Updated Plan',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($plan->fresh()->name)->toBe('Updated Plan');
});

test('non super admin cannot edit a plan', function () {
    $this->actingAs($this->tenantUser);

    $plan = Plan::factory()->create();

    Livewire::test(EditPlan::class, ['record' => $plan->id])
        ->assertForbidden();
});

test('is_public flag works correctly', function () {
    $this->actingAs($this->superAdmin);

    Livewire::test(CreatePlan::class)
        ->fillForm([
            'name' => 'Private Plan',
            'slug' => 'private-plan',
            'limits' => [
                'sms_monthly' => 100,
                'email_monthly' => 500,
                'ai_ingestion_monthly' => 50,
            ],
            'is_public' => false,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $plan = Plan::where('slug', 'private-plan')->first();
    expect($plan->is_public)->toBeFalse();

    // Public scope excludes it
    expect(Plan::public()->where('slug', 'private-plan')->exists())->toBeFalse();
});
