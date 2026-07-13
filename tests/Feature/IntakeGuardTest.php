<?php

use App\Models\Industry;
use App\Models\Lead;
use App\Models\MissedCall;
use App\Models\ShortLink;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->industry = Industry::factory()->create();
    $this->tenant = Tenant::factory()->create(['industry_id' => $this->industry->id]);
    Subscription::factory()->create(['tenant_id' => $this->tenant->id]);
});

// ─── Direct intake (shareable link) guards ──────────────────────

test('direct intake widget page returns 403 without session token', function () {
    $this->get("/intake/{$this->tenant->slug}/widget")
        ->assertForbidden();
});

test('short link resolves, sets token, and widget page loads', function () {
    $shortLink = ShortLink::forDirectLink($this->tenant);

    $this->get('/s/'.$shortLink->hash)
        ->assertRedirect("/intake/{$this->tenant->slug}/widget");

    $this->get("/intake/{$this->tenant->slug}/widget")
        ->assertOk();
});

test('short link resolution creates a lead', function () {
    $shortLink = ShortLink::forDirectLink($this->tenant);

    $this->get('/s/'.$shortLink->hash);

    $lead = Lead::latest()->first();
    expect($lead)->not->toBeNull()
        ->and($lead->tenant_id)->toBe($this->tenant->id)
        ->and($lead->source->value)->toBe('direct_link');
});

test('expired short link returns 410', function () {
    $shortLink = ShortLink::factory()->expired()->create([
        'tenant_id' => $this->tenant->id,
        'source' => 'direct_link',
        'expires_at' => now()->subHour(),
    ]);

    $this->get('/s/'.$shortLink->hash)
        ->assertStatus(410);
});

test('non-existent short link hash returns 404', function () {
    $this->get('/s/nonexistent')
        ->assertNotFound();
});

// ─── Missed call intake via short link ──────────────────────────

test('missed call widget page returns 403 without session token', function () {
    $missedCall = MissedCall::create([
        'tenant_id' => $this->tenant->id,
        'caller_number' => '+351912345678',
        'tenant_phone' => '+351210000001',
        'twilio_call_sid' => 'TEST-'.Str::random(16),
        'matched_by' => 'test',
    ]);

    $this->get("/missed-call/{$missedCall->id}/widget")
        ->assertForbidden();
});

test('missed call intake short link creates lead and redirects to widget', function () {
    $missedCall = MissedCall::create([
        'tenant_id' => $this->tenant->id,
        'caller_number' => '+351912345678',
        'tenant_phone' => '+351210000001',
        'twilio_call_sid' => 'TEST-'.Str::random(16),
        'matched_by' => 'test',
    ]);

    $shortLink = ShortLink::forMissedCallIntake($missedCall);

    $this->get('/s/'.$shortLink->hash)
        ->assertRedirect("/missed-call/{$missedCall->id}/widget");

    $this->get("/missed-call/{$missedCall->id}/widget")
        ->assertOk();
});

test('missed call intake short link reuses existing lead', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'industry_id' => $this->tenant->industry_id,
    ]);

    $missedCall = MissedCall::create([
        'tenant_id' => $this->tenant->id,
        'caller_number' => '+351912345678',
        'tenant_phone' => '+351210000001',
        'twilio_call_sid' => 'TEST-'.Str::random(16),
        'matched_by' => 'test',
        'lead_id' => $lead->id,
    ]);

    $shortLink = ShortLink::forMissedCallIntake($missedCall);

    $this->get('/s/'.$shortLink->hash)
        ->assertRedirect("/missed-call/{$missedCall->id}/widget");

    // Should reuse the existing lead, not create a new one
    expect(Lead::count())->toBe(1);
});

// ─── Missed call send-sms via short link ────────────────────────

test('missed call send-sms short link redirects to sent page', function () {
    $missedCall = MissedCall::create([
        'tenant_id' => $this->tenant->id,
        'caller_number' => '+351912345678',
        'tenant_phone' => '+351210000001',
        'twilio_call_sid' => 'TEST-'.Str::random(16),
        'matched_by' => 'test',
    ]);

    $shortLink = ShortLink::forMissedCallSendSms($missedCall);

    $this->get('/s/'.$shortLink->hash)
        ->assertRedirect("/missed-call/{$missedCall->id}/sent");
});
