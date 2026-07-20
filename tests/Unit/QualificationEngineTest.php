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
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'services' => ['roofing']]);

    expect($this->engine->isComplete($lead))->toBeFalse();
});

test('lead is complete when all required fields collected', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'services' => ['roofing']]);

    // Service required: problem_type, roof_type.
    // Qualification: property_type, urgency.
    // Contact: contact_name, phone, email, property_address, postal_code.
    foreach (['problem_type', 'roof_type', 'property_type', 'urgency', 'contact_name', 'phone', 'email', 'property_address', 'postal_code'] as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'text']);
    }

    expect($this->engine->isComplete($lead))->toBeTrue();
});

test('missing fields returns correct list', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'services' => ['roofing']]);
    $lead->fields()->create(['field_key' => 'contact_name', 'field_value' => 'João', 'field_type' => 'text']);

    $missing = $this->engine->getMissingFields($lead);

    // Service fields + qualification fields + remaining contact + optionals
    expect($missing)->toContain('problem_type', 'roof_type', 'property_type', 'urgency');
    expect($missing)->not->toContain('contact_name');
});

test('conditional requirements add fields when condition met', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'services' => ['roofing']]);
    $lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'replacement', 'field_type' => 'select']);

    $missing = $this->engine->getMissingFields($lead);

    expect($missing)->toContain('roof_size'); // Conditional: replacement → roof_size required
});

test('maybeComplete transitions status when complete', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'status' => LeadStatus::InProgress, 'services' => ['roofing']]);

    foreach (['problem_type', 'roof_type', 'property_type', 'urgency', 'contact_name', 'phone', 'email', 'property_address', 'postal_code'] as $key) {
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
        'services' => ['roofing'],
    ]);

    // No fields collected — first missing is problem_type (service fields come first)
    $next = $this->engine->getNextField($lead);

    expect($next)->not->toBeNull();
    expect($next['key'])->toBe('problem_type');
    expect($next['type'])->toBe('select');
});

test('getNextField advances after fields are collected', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'services' => ['roofing'],
    ]);

    // Collect problem_type and roof_type (service fields)
    $lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'repair', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'roof_type', 'field_value' => 'tile', 'field_type' => 'select']);

    $next = $this->engine->getNextField($lead);

    // Next should be property_type (first qualification shared field)
    expect($next['key'])->toBe('property_type');
    expect($next['type'])->toBe('select');
});

test('getNextField returns null when all fields collected', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'services' => ['roofing'],
    ]);

    // Collect ALL fields: required + contact + optionals
    $allFields = ['contact_name', 'phone', 'email', 'property_address', 'postal_code', 'notes',
        'problem_type', 'roof_type',
        'property_type', 'roof_age', 'urgency', 'insurance_claim',
        'leak_location', 'asbestos_removal_required', 'house_type', 'roof_size', 'material_supplied'];
    foreach ($allFields as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'text']);
    }

    // Upload a photo so the optional photos field is also considered collected
    $lead->addMediaFromString(fakeImagePng())
        ->usingFileName('test.png')
        ->toMediaCollection('photos');

    expect($this->engine->getNextField($lead))->toBeNull();
});

test('getNextField includes options for select fields', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'services' => ['roofing'],
    ]);

    // First field is problem_type (service select) — no need to collect anything first
    $next = $this->engine->getNextField($lead);

    expect($next['options'])->toBeArray();
    expect($next['options'])->not->toBeEmpty();
    // Each option should have value and label
    expect($next['options'][0])->toHaveKeys(['value', 'label']);
});

test('getNextField does not include options for text fields', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'services' => ['roofing'],
    ]);

    // Collect all select fields (service + qualification + optionals) so next is text.
    // Also upload photos so the file field doesn't come before text fields.
    $lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'repair', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'roof_type', 'field_value' => 'tile', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'property_type', 'field_value' => 'house', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'urgency', 'field_value' => 'within_week', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'roof_age', 'field_value' => 'less_than_5', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'insurance_claim', 'field_value' => 'no', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'material_supplied', 'field_value' => 'specialist_provides', 'field_type' => 'select']);

    $lead->addMediaFromString(fakeImagePng())
        ->usingFileName('test.png')
        ->toMediaCollection('photos');

    $next = $this->engine->getNextField($lead);

    expect($next['type'])->toBe('text');
    expect($next)->not->toHaveKey('options');
});

test('getNextField returns correct field after partial address collection', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'services' => ['roofing'],
    ]);

    // Collect service fields and contact_name
    $lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'repair', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'roof_type', 'field_value' => 'tile', 'field_type' => 'select']);
    $lead->fields()->create(['field_key' => 'contact_name', 'field_value' => 'Pedro', 'field_type' => 'text']);

    $next = $this->engine->getNextField($lead);

    // Order: service → qualification → contact. After service fields + contact_name,
    // next is property_type (first qualification field), not another contact field.
    expect($next['key'])->toBe('property_type');
    expect($next['type'])->toBe('select');
});

// --- File fields ---

test('file field is collected when media exists', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'services' => ['roofing']]);

    // Add a fake image to the photos collection (must pass mime-type validation)
    $lead->addMediaFromString(fakeImagePng())
        ->usingFileName('test.png')
        ->toMediaCollection('photos');

    // Collect all other required fields
    foreach (['problem_type', 'roof_type', 'property_type', 'urgency', 'contact_name', 'phone', 'email', 'property_address', 'postal_code'] as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'text']);
    }

    // Photos is optional, not required — but it should not block completion
    expect($this->engine->isComplete($lead))->toBeTrue();

    // getMissingFields should NOT include photos since media exists
    $missing = $this->engine->getMissingFields($lead);
    expect($missing)->not->toContain('photos');
});

test('file field is missing when no media uploaded', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'services' => ['roofing']]);

    // Collect all required fields but no photos
    foreach (['problem_type', 'roof_type', 'property_type', 'urgency', 'contact_name', 'phone', 'email', 'property_address', 'postal_code'] as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'text']);
    }

    // All other optional fields
    foreach (['roof_age', 'insurance_claim', 'material_supplied'] as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'select']);
    }

    // Photos is optional and not uploaded — should appear in missing
    $missing = $this->engine->getMissingFields($lead);
    expect($missing)->toContain('photos');
});

test('getNextField returns file type for photos field', function () {
    $lead = Lead::factory()->create(['tenant_id' => $this->tenant->id, 'services' => ['roofing']]);

    // Collect everything except optional fields (so photos is the next optional)
    foreach (['problem_type', 'roof_type', 'property_type', 'urgency',
        'roof_age', 'insurance_claim', 'material_supplied',
        'contact_name', 'phone', 'email', 'property_address', 'postal_code'] as $key) {
        $lead->fields()->create(['field_key' => $key, 'field_value' => "test_{$key}", 'field_type' => 'text']);
    }

    $next = $this->engine->getNextField($lead);

    expect($next['key'])->toBe('photos');
    expect($next['type'])->toBe('file');
    // File fields should not have options
    expect($next)->not->toHaveKey('options');
    expect($next)->not->toHaveKey('multi');
});

test('isComplete respects file field in conditional requirements', function () {
    $tenant = $this->tenant;
    // Set up a conditional requirement: if problem_type=emergency, require photos
    $tenant->update([
        'service_config' => [
            'roofing' => [
                'conditional_requirements' => [
                    ['when' => ['problem_type' => 'emergency'], 'require' => ['photos']],
                ],
            ],
        ],
    ]);

    $lead = Lead::factory()->create(['tenant_id' => $tenant->id, 'services' => ['roofing']]);

    // Collect all fields including problem_type=emergency but NO photos
    foreach (['problem_type', 'roof_type', 'property_type', 'urgency', 'contact_name', 'phone', 'email', 'property_address', 'postal_code'] as $key) {
        $value = $key === 'problem_type' ? 'emergency' : "test_{$key}";
        $lead->fields()->create(['field_key' => $key, 'field_value' => $value, 'field_type' => 'text']);
    }

    // Should be incomplete because photos is conditionally required but not uploaded
    $engine = new QualificationEngine(new IndustryConfigEngine);
    expect($engine->isComplete($lead))->toBeFalse();

    // Upload a photo — should now be complete
    $lead->addMediaFromString(fakeImagePng())
        ->usingFileName('test.png')
        ->toMediaCollection('photos');

    // reload to clear cached relations
    $lead->refresh();
    $lead->load('fields');

    expect($engine->isComplete($lead))->toBeTrue();
});
