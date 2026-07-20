<?php

use App\Models\Industry;
use App\Models\Tenant;
use App\Services\IndustryConfigEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->engine = new IndustryConfigEngine;
    $this->industry = Industry::factory()->create();
});

test('resolves base config without service', function () {
    $tenant = Tenant::factory()->create([
        'industry_id' => $this->industry->id,
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
        'industry_id' => $this->industry->id,
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
        'industry_id' => $this->industry->id,
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
        'industry_id' => $this->industry->id,
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
        'industry_id' => $this->industry->id,
        'active_services' => ['roofing', 'painting'],
    ]);

    $services = $this->engine->getAvailableServices($tenant);

    expect($services)->toHaveCount(2);
    expect($services[0])->toHaveKeys(['key', 'name', 'icon']);
    expect(collect($services)->pluck('key')->toArray())->toEqual(['roofing', 'painting']);
});
