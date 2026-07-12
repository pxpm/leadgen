<?php

use App\Enums\LeadStatus;
use App\Jobs\GenerateSummaryJob;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\QualificationEngine;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->industry = Industry::factory()->create();
    $this->tenant = Tenant::factory()->create(['industry_id' => $this->industry->id]);
    Subscription::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('widget config returns tenant branding and field definitions', function () {
    $response = $this->getJson("/api/widget/{$this->tenant->slug}/config");

    $response->assertOk()
        ->assertJsonPath('tenant.name', $this->tenant->name)
        ->assertJsonPath('tenant.locale', 'pt')
        ->assertJsonStructure(['greeting', 'field_definitions']);
});

test('widget config returns 404 for invalid slug', function () {
    $this->getJson('/api/widget/nonexistent/config')->assertNotFound();
});

test('start conversation creates a lead and returns session token', function () {
    $response = $this->postJson("/api/widget/{$this->tenant->slug}/conversations");

    $response->assertCreated()
        ->assertJsonStructure(['lead' => ['id', 'session_token']]);

    $token = $response->json('lead.session_token');
    expect(Lead::where('session_token', $token)->exists())->toBeTrue();
});

test('send message stores message and returns reply', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => LeadStatus::New,
    ]);

    $response = $this->postJson("/api/widget/conversations/{$lead->session_token}/messages", [
        'message' => 'Tenho uma infiltração no telhado',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['reply', 'is_complete', 'progress', 'lead']);

    expect($lead->messages()->count())->toBeGreaterThanOrEqual(1);
    expect($lead->fresh()->status)->toBe(LeadStatus::InProgress);
});

test('send message returns 410 for delivered lead', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => LeadStatus::Delivered,
    ]);

    $this->postJson("/api/widget/conversations/{$lead->session_token}/messages", [
        'message' => 'Hello',
    ])->assertStatus(410);
});

test('resume conversation restores state', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => LeadStatus::InProgress,
    ]);

    $lead->messages()->create(['role' => 'user', 'content' => 'Olá']);

    $response = $this->getJson("/api/widget/conversations/{$lead->session_token}");

    $response->assertOk()
        ->assertJsonPath('lead.id', $lead->id)
        ->assertJsonCount(1, 'messages');
});

test('upload photo stores media', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => LeadStatus::InProgress,
    ]);

    $response = $this->postJson("/api/widget/conversations/{$lead->session_token}/uploads", [
        'file' => UploadedFile::fake()->image('roof.jpg'),
    ]);

    $response->assertCreated()->assertJsonStructure(['id', 'url', 'name']);
});

test('full conversation flow: service selection → qualification → skip → summary', function () {
    // Start conversation
    $start = $this->postJson("/api/widget/{$this->tenant->slug}/conversations");
    $start->assertCreated();
    $token = $start->json('lead.session_token');

    // Service selection
    $sel = $this->postJson("/api/widget/conversations/{$token}/messages", [
        'message' => 'Telhados',
        'service_keys' => ['roofing'],
    ]);
    $sel->assertOk()
        ->assertJsonPath('phase', 'qualification')
        ->assertJsonPath('is_complete', false);

    // Answer name
    $r1 = $this->postJson("/api/widget/conversations/{$token}/messages", [
        'message' => 'Pedro',
    ]);
    $r1->assertOk()->assertJsonPath('is_complete', false);

    // Answer phone
    $r2 = $this->postJson("/api/widget/conversations/{$token}/messages", [
        'message' => '912345678',
    ]);
    $r2->assertOk()->assertJsonPath('is_complete', false);

    // Answer email
    $r3 = $this->postJson("/api/widget/conversations/{$token}/messages", [
        'message' => 'teste@email.com',
    ]);
    $r3->assertOk()->assertJsonPath('is_complete', false);

    // Answer address
    $r4 = $this->postJson("/api/widget/conversations/{$token}/messages", [
        'message' => 'Rua das Flores, 123',
    ]);
    $r4->assertOk()->assertJsonPath('is_complete', false);

    // Skip postal code
    $r5 = $this->postJson("/api/widget/conversations/{$token}/messages", [
        'message' => '__skip__',
    ]);
    $r5->assertOk();

    // The conversation is still going — not stuck
    expect($r5->json('is_complete'))->toBeFalse();
    expect($r5->json('phase'))->toBe('qualification');
});

test('GenerateSummaryJob is dispatched when lead qualifies', function () {
    Queue::fake();

    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => LeadStatus::InProgress,
        'service_type' => 'roofing',
    ]);

    // Collect all required fields so the lead qualifies
    foreach (['contact_name', 'phone', 'email', 'property_address', 'problem_type', 'roof_type'] as $key) {
        $lead->fields()->create([
            'field_key' => $key,
            'field_value' => 'test',
            'field_type' => 'text',
            'confidence' => 0.9,
            'is_required' => true,
        ]);
    }

    $engine = app(QualificationEngine::class);
    $engine->maybeComplete($lead);

    Queue::assertPushed(GenerateSummaryJob::class);
});
