<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
});

// --- User model ---

test('super admin user returns true for isSuperAdmin', function () {
    $user = User::factory()->create([
        'is_super_admin' => true,
        'tenant_id' => null,
    ]);

    expect($user->isSuperAdmin())->toBeTrue();
    expect($user->is_super_admin)->toBeTrue();
});

test('tenant user returns false for isSuperAdmin', function () {
    $user = User::factory()->create([
        'is_super_admin' => false,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($user->isSuperAdmin())->toBeFalse();
});

test('default user is not super admin', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    expect($user->isSuperAdmin())->toBeFalse();
    expect($user->is_super_admin)->toBeFalse();
});

test('user belongs to tenant', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    expect($user->tenant->id)->toBe($this->tenant->id);
});

test('super admin has null tenant', function () {
    $user = User::factory()->create(['is_super_admin' => true, 'tenant_id' => null]);

    expect($user->tenant_id)->toBeNull();
});
