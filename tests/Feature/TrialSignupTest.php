<?php

declare(strict_types=1);

use App\Models\DemoRequest;
use App\Models\Industry;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Industry::factory()->create(['slug' => 'construcao_civil', 'name' => 'Construção Civil', 'is_active' => true]);
    Plan::factory()->create(['slug' => 'trial', 'name' => 'Trial', 'monthly_price' => 0, 'yearly_price_per_month' => 0, 'is_public' => false, 'is_active' => true]);
});

test('trial signup creates tenant, user, and subscription', function () {
    $response = $this->postJson('/trial-signup', [
        'name' => 'João Silva',
        'email' => 'joao@empresa.pt',
        'phone' => '912345678',
        'company' => 'Empresa Teste Lda',
        'industry' => 'construcao_civil']);

    $response->assertCreated()
        ->assertJson(['ok' => true]);

    // Tenant was created
    $tenant = Tenant::where('name', 'Empresa Teste Lda')->first();
    expect($tenant)->not->toBeNull()
        ->and($tenant->slug)->toBe('empresa-teste-lda')
        ->and($tenant->industries()->first()->id)->toBe(1);

    // User was created
    $user = User::where('email', 'joao@empresa.pt')->first();
    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe($tenant->id)
        ->and($user->is_super_admin)->toBeFalse();

    // Subscription was created with trial status
    $subscription = Subscription::where('tenant_id', $tenant->id)->first();
    expect($subscription)->not->toBeNull()
        ->and($subscription->status->value)->toBe('trialing')
        ->and($subscription->trial_ends_at)->not->toBeNull()
        ->and($subscription->plan->slug)->toBe('trial');
});

test('trial signup validates required fields', function () {
    $response = $this->postJson('/trial-signup', []);

    $response->assertStatus(422)
        ->assertJson(fn ($json) => $json->has('errors.name')->has('errors.email')->has('errors.company')->has('errors.industry')->etc());
});

test('trial signup rejects duplicate email', function () {
    $industry = Industry::first();
    $tenant = Tenant::factory()->create([]);
    User::factory()->create(['email' => 'joao@empresa.pt', 'tenant_id' => $tenant->id]);

    $response = $this->postJson('/trial-signup', [
        'name' => 'João Silva',
        'email' => 'joao@empresa.pt',
        'phone' => '912345678',
        'company' => 'Outra Empresa',
        'industry' => 'construcao_civil']);

    $response->assertStatus(422)
        ->assertJson(fn ($json) => $json->has('message')->etc());
});

test('trial signup rejects invalid industry', function () {
    $response = $this->postJson('/trial-signup', [
        'name' => 'João Silva',
        'email' => 'joao@empresa.pt',
        'phone' => '912345678',
        'company' => 'Empresa Teste',
        'industry' => 'nonexistent_industry']);

    $response->assertStatus(422)
        ->assertJson(fn ($json) => $json->has('errors.industry')->etc());
});

test('trial signup with outro creates demo request instead of tenant', function () {
    $response = $this->postJson('/trial-signup', [
        'name' => 'João Silva',
        'email' => 'joao@empresa.pt',
        'phone' => '912345678',
        'company' => 'Empresa Pintura Lda',
        'industry' => 'outro',
        'industry_other' => 'Pintura Automóvel']);

    $response->assertCreated()
        ->assertJson(['ok' => true, 'outro' => true]);

    // Demo request was created, not a tenant
    $demo = DemoRequest::where('email', 'joao@empresa.pt')->first();
    expect($demo)->not->toBeNull()
        ->and($demo->industry)->toBe('Pintura Automóvel')
        ->and($demo->status)->toBe('new');

    // No tenant was created
    expect(Tenant::where('name', 'Empresa Pintura Lda')->exists())->toBeFalse();
});

test('trial signup with outro requires industry_other', function () {
    $response = $this->postJson('/trial-signup', [
        'name' => 'João Silva',
        'email' => 'joao@empresa.pt',
        'phone' => '912345678',
        'company' => 'Empresa Teste',
        'industry' => 'outro']);

    $response->assertStatus(422)
        ->assertJson(fn ($json) => $json->has('errors.industry_other')->etc());
});

test('honeypot field returns fake success and bans IP', function () {
    $response = $this->postJson('/trial-signup', [
        'name' => 'Bot',
        'email' => 'bot@spam.com',
        'company' => 'Spam Inc',
        'industry' => 'construcao_civil',
        'website' => 'http://spam-link.com', // honeypot filled by bot
    ]);

    // Returns fake 201 to not tip off the bot
    $response->assertCreated()
        ->assertJson(['ok' => true]);

    // No tenant or user was created
    expect(Tenant::where('name', 'Spam Inc')->exists())->toBeFalse();
    expect(User::where('email', 'bot@spam.com')->exists())->toBeFalse();

    // IP is banned
    expect(Cache::get('banned_ip:127.0.0.1'))->toBeTrue();
});

test('trial signup creates unique slug when duplicate company name', function () {
    // Create existing tenant with same slug
    Tenant::factory()->create(['name' => 'Empresa Teste', 'slug' => 'empresa-teste']);

    $response = $this->postJson('/trial-signup', [
        'name' => 'João Silva',
        'email' => 'joao@empresa.pt',
        'phone' => '912345678',
        'company' => 'Empresa Teste',
        'industry' => 'construcao_civil']);

    $response->assertCreated();

    $tenant = Tenant::where('slug', 'empresa-teste-1')->first();
    expect($tenant)->not->toBeNull();
});

test('expire trials command cancels expired subscriptions', function () {
    $industry = Industry::first();
    $tenant = Tenant::factory()->create([]);
    $trialPlan = Plan::where('slug', 'trial')->first();
    User::factory()->create(['tenant_id' => $tenant->id]);

    // Active trial (not expired)
    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $trialPlan->id,
        'status' => 'trialing',
        'trial_ends_at' => now()->addDays(5)]);

    // Expired trial
    $tenant2 = Tenant::factory()->create(['slug' => 'tenant-2']);
    User::factory()->create(['tenant_id' => $tenant2->id, 'email' => 'other@test.pt']);
    $expiredSub = Subscription::factory()->create([
        'tenant_id' => $tenant2->id,
        'plan_id' => $trialPlan->id,
        'status' => 'trialing',
        'trial_ends_at' => now()->subDays(1)]);

    $this->artisan('trials:expire')
        ->assertSuccessful();

    // Active trial still active
    expect(Subscription::find(1)->status->value)->toBe('trialing');

    // Expired trial now canceled
    expect($expiredSub->fresh()->status->value)->toBe('canceled');
});
