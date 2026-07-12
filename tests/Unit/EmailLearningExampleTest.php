<?php

declare(strict_types=1);

use App\Enums\FollowUpScenario;
use App\Models\EmailLearningExample;
use App\Models\FollowUpAction;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->lead = Lead::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('recordFromAction creates learning example when email was edited', function () {
    $action = FollowUpAction::create([
        'tenant_id' => $this->tenant->id,
        'lead_id' => $this->lead->id,
        'scenario' => FollowUpScenario::Decline,
        'selected_items' => ['no_availability', 'out_of_area'],
        'generated_email' => 'Original AI text.',
        'final_email' => 'Edited by contractor.',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    EmailLearningExample::recordFromAction($action);

    $example = EmailLearningExample::first();
    expect($example)->not->toBeNull();
    expect($example->scenario)->toBe('decline');
    expect($example->was_edited)->toBeTrue();
    expect($example->generated_body)->toBe('Original AI text.');
    expect($example->sent_body)->toBe('Edited by contractor.');
});

test('recordFromAction skips when generated and final are identical', function () {
    $action = FollowUpAction::create([
        'tenant_id' => $this->tenant->id,
        'lead_id' => $this->lead->id,
        'scenario' => FollowUpScenario::Decline,
        'selected_items' => ['no_availability'],
        'generated_email' => 'Same text.',
        'final_email' => 'Same text.',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    EmailLearningExample::recordFromAction($action);

    $example = EmailLearningExample::first();
    expect($example)->not->toBeNull();
    expect($example->was_edited)->toBeFalse();
});

test('findSimilar returns matching examples for same scenario and reasons', function () {
    // Create examples with different reasons
    EmailLearningExample::create([
        'tenant_id' => $this->tenant->id,
        'scenario' => 'decline',
        'reasons_hash' => md5('no_availability,out_of_area'),
        'generated_body' => 'Old generated.',
        'sent_body' => 'Contractor style A.',
        'was_edited' => true,
    ]);

    EmailLearningExample::create([
        'tenant_id' => $this->tenant->id,
        'scenario' => 'decline',
        'reasons_hash' => md5('budget_mismatch'),
        'generated_body' => 'Other scenario.',
        'sent_body' => 'Different reasons.',
        'was_edited' => true,
    ]);

    $results = EmailLearningExample::findSimilar(
        tenantId: $this->tenant->id,
        scenario: FollowUpScenario::Decline,
        reasons: ['out_of_area', 'no_availability'], // order shouldn't matter
    );

    expect($results)->toHaveCount(1);
    expect($results[0]['body'])->toBe('Contractor style A.');
});

test('findSimilar only returns edited examples', function () {
    EmailLearningExample::create([
        'tenant_id' => $this->tenant->id,
        'scenario' => 'decline',
        'reasons_hash' => md5('no_availability'),
        'generated_body' => 'Same.',
        'sent_body' => 'Same.',
        'was_edited' => false,
    ]);

    $results = EmailLearningExample::findSimilar(
        tenantId: $this->tenant->id,
        scenario: FollowUpScenario::Decline,
        reasons: ['no_availability'],
    );

    expect($results)->toBeEmpty();
});

test('findSimilar scopes to tenant', function () {
    $otherTenant = Tenant::factory()->create();

    EmailLearningExample::create([
        'tenant_id' => $otherTenant->id,
        'scenario' => 'decline',
        'reasons_hash' => md5('no_availability'),
        'generated_body' => 'Other tenant.',
        'sent_body' => 'Other tenant style.',
        'was_edited' => true,
    ]);

    $results = EmailLearningExample::findSimilar(
        tenantId: $this->tenant->id,
        scenario: FollowUpScenario::Decline,
        reasons: ['no_availability'],
    );

    expect($results)->toBeEmpty();
});
