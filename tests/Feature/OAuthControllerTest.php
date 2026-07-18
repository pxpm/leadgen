<?php

declare(strict_types=1);

use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantEmailAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    Subscription::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'email' => 'admin@test.com',
    ]);

    $this->actingAs($this->user);
});

// ─── CSRF State Protection ─────────────────────────────────────

test('google redirect stores csrf state in session', function () {
    // Mock Socialite redirect to avoid session store requirement on API routes
    $mockRedirect = redirect('https://accounts.google.com/o/oauth2/auth');
    $provider = Mockery::mock();
    $provider->shouldReceive('scopes')->andReturnSelf();
    $provider->shouldReceive('with')->andReturnSelf();
    $provider->shouldReceive('redirect')->andReturn($mockRedirect);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->withSession([])->get('/api/oauth/google/redirect');

    expect(session()->get('google_oauth_state'))->not->toBeNull()
        ->and(strlen(session()->get('google_oauth_state')))->toBe(40);
});

test('microsoft redirect stores csrf state in session', function () {
    $mockRedirect = redirect('https://login.microsoftonline.com/common/oauth2/v2.0/authorize');
    $provider = Mockery::mock();
    $provider->shouldReceive('scopes')->andReturnSelf();
    $provider->shouldReceive('with')->andReturnSelf();
    $provider->shouldReceive('redirect')->andReturn($mockRedirect);
    Socialite::shouldReceive('driver')->with('microsoft')->andReturn($provider);

    $this->withSession([])->get('/api/oauth/microsoft/redirect');

    expect(session()->get('ms_oauth_state'))->not->toBeNull()
        ->and(strlen(session()->get('ms_oauth_state')))->toBe(40);
});

// ─── Google OAuth Callback ─────────────────────────────────────

test('google callback with valid state creates email account', function () {
    $state = 'test-state-'.bin2hex(random_bytes(8));
    session(['google_oauth_state' => $state]);

    $fakeUser = (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Test User',
        'email' => 'test@gmail.com',
    ])->setToken('fake-access-token')
        ->setRefreshToken('fake-refresh-token')
        ->setExpiresIn(3600)
        ->setApprovedScopes(['openid', 'profile', 'email', 'https://www.googleapis.com/auth/gmail.send']);

    Socialite::shouldReceive('driver')->with('google')->andReturn(new class($fakeUser)
    {
        public function __construct(private SocialiteUser $user) {}

        public function user(): SocialiteUser
        {
            return $this->user;
        }
    });

    $this->get('/api/oauth/google/callback?state='.$state.'&code=valid')
        ->assertRedirect('/manage-backoffice')
        ->assertSessionHas('success');

    $account = TenantEmailAccount::where('tenant_id', $this->tenant->id)
        ->where('email', 'test@gmail.com')
        ->first();

    expect($account)->not->toBeNull()
        ->and($account->provider)->toBe('google')
        ->and($account->status)->toBe('active')
        ->and($account->verified_at)->not->toBeNull();
});

test('google callback with invalid state is rejected', function () {
    session(['google_oauth_state' => 'real-state']);

    $response = $this->get('/api/oauth/google/callback?state=attacker-state&code=stolen');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('google callback with user denial shows error', function () {
    session(['google_oauth_state' => 'some-state']);

    $response = $this->get('/api/oauth/google/callback?state=some-state&error=access_denied');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

// ─── Microsoft OAuth Callback ──────────────────────────────────

test('microsoft callback with invalid state is rejected', function () {
    session(['ms_oauth_state' => 'real-ms-state']);

    $response = $this->get('/api/oauth/microsoft/callback?state=attacker-state&code=stolen');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('microsoft callback with user denial shows error', function () {
    session(['ms_oauth_state' => 'ms-state']);

    $response = $this->get('/api/oauth/microsoft/callback?state=ms-state&error=access_denied');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});
