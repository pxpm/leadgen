<?php

use App\Enums\LeadStatus;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->industry = Industry::factory()->create();
    $this->tenant = Tenant::factory()->create(['industry_id' => $this->industry->id]);
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
