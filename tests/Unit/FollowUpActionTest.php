<?php

declare(strict_types=1);

use App\Enums\FollowUpScenario;
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

test('casts scenario to FollowUpScenario enum', function () {
    $action = FollowUpAction::create([
        'tenant_id' => $this->tenant->id,
        'lead_id' => $this->lead->id,
        'scenario' => FollowUpScenario::Decline,
        'selected_items' => ['no_availability'],
        'status' => 'draft',
    ]);

    expect($action->scenario)->toBeInstanceOf(FollowUpScenario::class);
    expect($action->scenario)->toBe(FollowUpScenario::Decline);
});

test('casts selected_items to array', function () {
    $action = FollowUpAction::create([
        'tenant_id' => $this->tenant->id,
        'lead_id' => $this->lead->id,
        'scenario' => FollowUpScenario::RequestInfo,
        'selected_items' => ['photos', 'dimensions'],
        'status' => 'draft',
    ]);

    expect($action->selected_items)->toBeArray();
    expect($action->selected_items)->toContain('photos', 'dimensions');
});

test('isDraft returns true for draft status', function () {
    $action = FollowUpAction::create([
        'tenant_id' => $this->tenant->id,
        'lead_id' => $this->lead->id,
        'scenario' => FollowUpScenario::Decline,
        'selected_items' => [],
        'status' => 'draft',
    ]);

    expect($action->isDraft())->toBeTrue();
    expect($action->isSent())->toBeFalse();
});

test('isSent returns true for sent status', function () {
    $action = FollowUpAction::create([
        'tenant_id' => $this->tenant->id,
        'lead_id' => $this->lead->id,
        'scenario' => FollowUpScenario::Decline,
        'selected_items' => [],
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    expect($action->isSent())->toBeTrue();
    expect($action->isDraft())->toBeFalse();
});

test('markSent updates status and timestamp', function () {
    $action = FollowUpAction::create([
        'tenant_id' => $this->tenant->id,
        'lead_id' => $this->lead->id,
        'scenario' => FollowUpScenario::Decline,
        'selected_items' => [],
        'status' => 'draft',
    ]);

    $action->markSent();

    expect($action->fresh()->status)->toBe('sent');
    expect($action->fresh()->sent_at)->not->toBeNull();
});
