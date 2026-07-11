<?php

use App\Enums\LeadStatus;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Tenant;
use App\Services\IndustryConfigEngine;
use App\Services\QualificationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->industry = Industry::factory()->create(['config' => require database_path('seeders/data/industries/construcao_civil.php')]);
    $this->tenant = Tenant::factory()->create(['industry_id' => $this->industry->id]);
    $this->engine = new QualificationEngine(new IndustryConfigEngine);
});

test('lead is not complete when no fields collected', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'service_type' => 'roofing']);

    expect($this->engine->isComplete($lead))->toBeFalse();
});

test('lead is complete when all required fields collected', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'service_type' => 'roofing']);

    // Service required: problem_type, roof_type. Shared required: contact_name, phone, email, property_address.
    foreach (['contact_name', 'phone', 'email', 'property_address', 'problem_type', 'roof_type'] as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'text']);
    }

    expect($this->engine->isComplete($lead))->toBeTrue();
});

test('missing fields returns correct list', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'service_type' => 'roofing']);
    $lead->fields()->create(['field_key' => 'contact_name', 'field_value' => 'João', 'field_type' => 'text']);

    $missing = $this->engine->getMissingFields($lead);

    // Service: problem_type, roof_type. Shared: property_address. Contact: phone, email.
    expect($missing)->toContain('problem_type', 'roof_type', 'property_address');
    expect($missing)->not->toContain('contact_name');
});

test('conditional requirements add fields when condition met', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'service_type' => 'roofing']);
    $lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'replacement', 'field_type' => 'select']);

    $missing = $this->engine->getMissingFields($lead);

    expect($missing)->toContain('roof_size'); // Conditional: replacement → roof_size required
});

test('maybeComplete transitions status when complete', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'status' => LeadStatus::InProgress, 'service_type' => 'roofing']);

    foreach (['contact_name', 'phone', 'email', 'property_address', 'problem_type', 'roof_type'] as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'text']);
    }

    $this->engine->maybeComplete($lead);

    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified);
    expect($lead->fresh()->qualified_at)->not->toBeNull();
});

// --- getNextField ---

test('getNextField returns first missing field', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'service_type' => 'roofing',
    ]);

    // No fields collected — first missing is contact_name (shared fields come first)
    $next = $this->engine->getNextField($lead);

    expect($next)->not->toBeNull();
    expect($next['key'])->toBe('contact_name');
    expect($next['type'])->toBe('text');
});

test('getNextField advances after fields are collected', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'service_type' => 'roofing',
    ]);

    // Collect problem_type and roof_type (service fields)
    $lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'repair', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'roof_type', 'field_value' => 'tile', 'field_type' => 'select']);

    $next = $this->engine->getNextField($lead);

    // Next should be a shared field: contact_name or property_address
    expect($next['key'])->toBe('contact_name');
    expect($next['type'])->toBe('text');
});

test('getNextField returns null when all fields collected', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'service_type' => 'roofing',
    ]);

    // Collect ALL fields: required + contact + optionals
    $allFields = ['contact_name', 'phone', 'email', 'property_address', 'postal_code', 'notes',
        'problem_type', 'roof_type',
        'property_type', 'roof_age', 'urgency', 'insurance_claim',
        'leak_location', 'asbestos_removal_required', 'house_type', 'roof_size', 'material_supplied'];
    foreach ($allFields as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'text']);
    }

    expect($this->engine->getNextField($lead))->toBeNull();
});

test('getNextField includes options for select fields', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'service_type' => 'roofing',
    ]);

    // Collect shared text fields first so next is a service select field
    foreach (['contact_name', 'phone', 'email', 'property_address'] as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => 'test', 'field_type' => 'text']);
    }

    $next = $this->engine->getNextField($lead);

    expect($next['options'])->toBeArray();
    expect($next['options'])->not->toBeEmpty();
    // Each option should have value and label
    expect($next['options'][0])->toHaveKeys(['value', 'label']);
});

test('getNextField does not include options for text fields', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'service_type' => 'roofing',
    ]);

    // Collect all select fields so next is text
    $lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'repair', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'roof_type', 'field_value' => 'tile', 'field_type' => 'select']);

    $next = $this->engine->getNextField($lead);

    expect($next['type'])->toBe('text');
    expect($next)->not->toHaveKey('options');
});

test('getNextField returns correct field after partial address collection', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'service_type' => 'roofing',
    ]);

    // Collect service fields and contact_name
    $lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'repair', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'roof_type', 'field_value' => 'tile', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'contact_name', 'field_value' => 'Pedro', 'field_type' => 'text']);

    $next = $this->engine->getNextField($lead);

    // Next should be the first missing required field in order.
    // Order: contact_name, phone, email, property_address, then service fields.
    // After collecting problem_type, roof_type, contact_name → next is 'phone'.
    expect($next['key'])->toBe('phone');
    expect($next['type'])->toBe('text');
});
