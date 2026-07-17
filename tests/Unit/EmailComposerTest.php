<?php

declare(strict_types=1);

use App\Enums\FollowUpScenario;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Tenant;
use App\Services\EmailComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->industry = Industry::factory()->create([
        'config' => require database_path('seeders/data/industries/construcao_civil.php'),
    ]);
    $this->tenant = Tenant::factory()->create([
        'industry_id' => $this->industry->id,
        'locale' => 'pt',
    ]);
    $this->lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'industry_id' => $this->industry->id,
        'services' => ['roofing'],
    ]);

    // Pre-collect some lead details for context
    $this->lead->fields()->createMany([
        ['field_key' => 'contact_name', 'field_value' => 'João Silva', 'field_type' => 'text'],
        ['field_key' => 'phone', 'field_value' => '912345678', 'field_type' => 'text'],
        ['field_key' => 'email', 'field_value' => 'joao@example.com', 'field_type' => 'text'],
        ['field_key' => 'property_address', 'field_value' => 'Rua das Flores, 123', 'field_type' => 'text'],
        ['field_key' => 'problem_type', 'field_value' => 'repair', 'field_type' => 'select'],
        ['field_key' => 'roof_type', 'field_value' => 'tile', 'field_type' => 'select'],
    ]);

    $this->composer = app(EmailComposer::class);
});

// --- Fallback Template Tests (no AI needed) ---

test('fallback decline email includes reasons', function () {
    // Force fallback by passing invalid provider
    config(['follow_up.ai.provider' => 'nonexistent']);

    $result = $this->composer->compose(
        lead: $this->lead,
        scenario: FollowUpScenario::Decline,
        selectedItems: ['no_availability', 'out_of_area'],
        tenantName: 'Telhados & Companhia',
    );

    expect($result)->toContain('João Silva');
    expect($result)->toContain('Telhados & Companhia');
    expect($result)->toContain('sem disponibilidade');
    expect($result)->toContain('fora da area');
});

test('fallback request info email lists requested items', function () {
    config(['follow_up.ai.provider' => 'nonexistent']);

    $result = $this->composer->compose(
        lead: $this->lead,
        scenario: FollowUpScenario::RequestInfo,
        selectedItems: ['photos', 'dimensions'],
        tenantName: 'Telhados & Companhia',
    );

    expect($result)->toContain('João Silva');
    expect($result)->toContain('Fotos do local');
    expect($result)->toContain('Medidas/área');
});

test('fallback quote followup changes tone per stage', function () {
    config(['follow_up.ai.provider' => 'nonexistent']);

    $first = $this->composer->compose(
        lead: $this->lead,
        scenario: FollowUpScenario::QuoteFollowUp,
        selectedItems: ['first_followup'],
        tenantName: 'Telhados & Companhia',
    );

    $final = $this->composer->compose(
        lead: $this->lead,
        scenario: FollowUpScenario::QuoteFollowUp,
        selectedItems: ['final_followup'],
        tenantName: 'Telhados & Companhia',
    );

    expect($first)->not->toBe($final);
    expect($first)->toContain('oportunidade');
    expect($final)->toContain('último contacto');
});

test('fallback general uses free text', function () {
    config(['follow_up.ai.provider' => 'nonexistent']);

    $result = $this->composer->compose(
        lead: $this->lead,
        scenario: FollowUpScenario::General,
        selectedItems: [],
        freeText: 'Quero agradecer o contacto e marcar uma visita.',
        tenantName: 'Telhados & Companhia',
    );

    expect($result)->toContain('Quero agradecer');
});

test('compose handles missing contact name gracefully', function () {
    config(['follow_up.ai.provider' => 'nonexistent']);
    $this->lead->fields()->where('field_key', 'contact_name')->delete();

    $result = $this->composer->compose(
        lead: $this->lead->fresh(),
        scenario: FollowUpScenario::Decline,
        selectedItems: ['job_too_small'],
        tenantName: 'Telhados & Companhia',
    );

    expect($result)->toContain('cliente'); // fallback word
    expect($result)->toContain('Telhados & Companhia');
});

test('buildUserPrompt includes lead details and excludes contact fields', function () {
    $reflection = new ReflectionClass(EmailComposer::class);
    $method = $reflection->getMethod('buildUserPrompt');

    $config = config('follow_up.scenarios.decline');

    $prompt = $method->invoke(
        $this->composer,
        $this->lead,
        FollowUpScenario::Decline,
        $config,
        ['no_availability'],
        null,
        'Telhados & Companhia',
        'pt',
    );

    expect($prompt)->toContain('João Silva');
    expect($prompt)->toContain('Telhados & Companhia');
    expect($prompt)->toContain('roofing');
    // Contact fields should NOT be in the prompt (they're in email headers)
    expect($prompt)->not->toContain('912345678');
    expect($prompt)->not->toContain('joao@example.com');
    expect($prompt)->not->toContain('Rua das Flores');
});
