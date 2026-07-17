<?php

declare(strict_types=1);

use App\Enums\LeadStatus;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Tenant;
use App\Services\QualificationEngine;

beforeEach(function () {
    $base = require database_path('seeders/data/industries/construcao_civil.php');
    $roofing = require database_path('seeders/data/industries/services/roofing.php');

    $config = $base;
    $config['required_fields'] = array_merge($roofing['required_fields'] ?? [], $base['shared_fields']['qualification'] ?? [], $base['shared_fields']['contact'] ?? []);
    $config['optional_fields'] = $roofing['optional_fields'] ?? [];
    $config['field_definitions'] = array_merge($config['field_definitions'] ?? [], $roofing['field_definitions'] ?? []);
    $config['conditional_fields'] = $roofing['conditional_fields'] ?? [];
    $config['conditional_requirements'] = $roofing['conditional_requirements'] ?? [];
    $config['locales'] = array_merge_recursive($config['locales'] ?? [], $roofing['locales'] ?? []);
    $config['service_name'] = $roofing['name'] ?? 'Roofing';

    $industry = Industry::factory()->create([
        'slug' => 'roofing',
        'config' => $config,
    ]);

    $this->tenant = Tenant::factory()->create([
        'industry_id' => $industry->id,
        'locale' => 'pt',
    ]);

    $this->lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'industry_id' => $industry->id,
        'services' => ['roofing'],
        'status' => LeadStatus::InProgress,
    ]);

    $this->engine = app(QualificationEngine::class);
});

test('conditional field appears when trigger condition is met', function () {
    $this->lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'leak', 'field_type' => 'select']);

    $missing = $this->engine->getMissingFields($this->lead);

    expect($missing)->toContain('leak_location');
});

test('conditional field hidden when trigger not met', function () {
    $this->lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'inspection', 'field_type' => 'select']);

    $missing = $this->engine->getMissingFields($this->lead);

    expect($missing)->not->toContain('leak_location');
});

test('OR in trigger values works', function () {
    $this->lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'repair', 'field_type' => 'select']);

    $missing = $this->engine->getMissingFields($this->lead);

    expect($missing)->toContain('leak_location');
});

test('required conditional blocks completion when triggered and missing', function () {
    $this->lead->fields()->create(['field_key' => 'roof_type', 'field_value' => 'asbestos', 'field_type' => 'select']);

    expect($this->engine->isComplete($this->lead))->toBeFalse();
});

test('optional conditional does not block completion', function () {
    $this->lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'leak', 'field_type' => 'select']);
    $this->lead->fields()->create(['field_key' => 'roof_type', 'field_value' => 'tile', 'field_type' => 'select']);
    $this->lead->fields()->create(['field_key' => 'property_type', 'field_value' => 'house', 'field_type' => 'select']);
    $this->lead->fields()->create(['field_key' => 'urgency', 'field_value' => 'within_week', 'field_type' => 'select']);
    $this->lead->fields()->create(['field_key' => 'contact_name', 'field_value' => 'Test', 'field_type' => 'text']);
    $this->lead->fields()->create(['field_key' => 'property_address', 'field_value' => 'Rua X', 'field_type' => 'text']);
    $this->lead->fields()->create(['field_key' => 'email', 'field_value' => 'a@b.com', 'field_type' => 'text']);
    $this->lead->fields()->create(['field_key' => 'phone', 'field_value' => '912345678', 'field_type' => 'text']);
    $this->lead->fields()->create(['field_key' => 'postal_code', 'field_value' => '1000-001', 'field_type' => 'text']);

    expect($this->engine->isComplete($this->lead))->toBeTrue();
});

test('disabled conditional field never appears', function () {
    $this->tenant->update(['service_config' => ['roofing' => ['field_definitions' => ['leak_location' => ['enabled' => false]]]]]);
    $this->lead->refresh();
    $this->lead->fields()->create(['field_key' => 'problem_type', 'field_value' => 'leak', 'field_type' => 'select']);

    $missing = $this->engine->getMissingFields($this->lead);

    expect($missing)->not->toContain('leak_location');
});
