<?php

use App\Models\Industry;
use App\Models\Tenant;
use App\Services\IndustryConfigEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->engine = new IndustryConfigEngine;
    $this->industry = Industry::factory()->create();
});

test('resolves base config without service', function () {
    $tenant = Tenant::factory()->create([
        'locale' => 'pt',
    ]);

    $config = $this->engine->resolve($tenant);
    $locale = $this->engine->getLocale($tenant);

    expect($locale)->toBe('pt');
    expect($config)->toHaveKeys(['shared_fields', 'field_definitions', 'locales']);
    expect($config['locales'])->toHaveKey('pt');
});

test('resolves config with service merges fields', function () {
    $tenant = Tenant::factory()->create([
        'locale' => 'pt',
        'active_services' => ['roofing'],
    ]);

    $config = $this->engine->resolve($tenant, 'roofing');

    expect($config)->toHaveKeys(['required_fields', 'contact_fields', 'locales']);
    expect($config['locales']['pt'])->toHaveKeys(['ai_prompt', 'synonyms']);
    expect($config['required_fields'])->toContain('problem_type', 'roof_type', 'property_type', 'urgency');
    expect($config['contact_fields'])->toContain('contact_name', 'phone', 'email', 'property_address', 'postal_code');
    expect($config['field_definitions'])->toHaveKeys(['roof_type', 'problem_type', 'contact_name', 'phone']);
});

test('tenant overrides merge correctly', function () {
    $tenant = Tenant::factory()->create([
        'qualification_overrides' => [
            'additional_required_fields' => ['insurance_claim'],
        ],
        'active_services' => ['roofing'],
    ]);

    $config = $this->engine->resolve($tenant, 'roofing');

    expect($config['required_fields'])->toContain('insurance_claim');
});

test('greeting message override applies to correct locale', function () {
    $tenant = Tenant::factory()->create([
        'locale' => 'pt',
        'qualification_overrides' => [
            'greeting_message' => 'Mensagem personalizada!',
        ],
    ]);

    $config = $this->engine->resolve($tenant);

    expect($config['locales']['pt']['ai_prompt']['greeting_message'])->toBe('Mensagem personalizada!');
});

test('getAvailableServices returns enabled services', function () {
    $tenant = Tenant::factory()->create([
        'active_services' => ['roofing', 'painting'],
    ]);

    $services = $this->engine->getAvailableServices($tenant);

    expect($services)->toHaveCount(2);
    expect($services[0])->toHaveKeys(['key', 'name', 'icon']);
    expect(collect($services)->pluck('key')->toArray())->toEqual(['roofing', 'painting']);
});

test('resolveMulti merges fields from multiple industries with deduplication', function () {
    // Use real industry slugs that have config files, but ensure uniqueness
    $plumbing = Industry::where('slug', 'plumbing')->first()
        ?? Industry::factory()->create(['slug' => 'plumbing', 'name' => 'Plumbing']);
    $hvac = Industry::where('slug', 'hvac')->first()
        ?? Industry::factory()->create(['slug' => 'hvac', 'name' => 'HVAC']);

    $tenant = Tenant::factory()->create(['locale' => 'pt']);
    $tenant->industries()->sync([$plumbing->id, $hvac->id]);

    $config = $this->engine->resolveMulti(
        $tenant,
        Collection::make([$plumbing, $hvac]),
        ['leak_repair']
    );

    // Shared qualification fields merged from both industries, deduplicated
    expect($config['shared_fields']['qualification'])->toContain('property_type', 'urgency');

    // Contact fields present
    expect($config['shared_fields']['contact'])->toContain('contact_name', 'phone', 'email');

    // Service fields from the selected service
    expect($config['required_fields'])->not->toBeEmpty();

    // Field definitions present
    expect($config['field_definitions'])->toHaveKeys(['contact_name', 'phone', 'email']);
});

test('resolveMulti throws when no industries provided', function () {
    $tenant = Tenant::factory()->create();

    expect(fn () => $this->engine->resolveMulti($tenant, Collection::make([]), []))
        ->toThrow(\RuntimeException::class);
});
