<?php

declare(strict_types=1);

use App\Enums\FollowUpScenario;
use App\Livewire\FollowUpComposer;
use App\Mail\FollowUpMail;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $industry = Industry::factory()->create();
    $this->tenant = Tenant::factory()->create([
        'locale' => 'pt',
    ]);
    $this->lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
    $this->lead->industries()->sync([$industry->id]);

    // Pre-collect email so send can work
    $this->lead->fields()->create([
        'field_key' => 'email',
        'field_value' => 'cliente@example.com',
        'field_type' => 'text',
    ]);
});

test('component mounts with correct scenario', function () {
    Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::Decline->value,
    ])
        ->assertSet('scenario', 'decline')
        ->assertSet('lead.id', $this->lead->id)
        ->assertSee('Indisponibilidade')
        ->assertSee('Ambito do trabalho');
});

test('generateEmail requires selected items for decline scenario', function () {
    Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::Decline->value,
    ])
        ->set('selectedItems', [])
        ->call('generateEmail')
        ->assertSet('generatedEmail', '');
});

test('generateEmail works with selected items', function () {
    // Use fallback mode (no real AI call)
    config(['follow_up.ai.provider' => 'nonexistent']);

    Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::Decline->value,
    ])
        ->set('selectedItems', ['no_availability'])
        ->call('generateEmail')
        ->assertSet('generatedEmail', fn ($val) => ! empty($val));
});

test('sendEmail dispatches mail and creates records', function () {
    config(['follow_up.ai.provider' => 'nonexistent']);

    Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::Decline->value,
    ])
        ->set('selectedItems', ['no_availability'])
        ->call('generateEmail')
        ->assertSet('generatedEmail', fn ($val) => ! empty($val))
        ->call('sendEmail')
        ->assertSet('emailSent', true);

    Mail::assertSent(FollowUpMail::class, function ($mail) {
        return $mail->lead->id === $this->lead->id;
    });

    // Check database records
    $this->assertDatabaseHas('follow_up_actions', [
        'lead_id' => $this->lead->id,
        'scenario' => 'decline',
        'status' => 'sent',
    ]);

    $this->assertDatabaseHas('email_learning_examples', [
        'tenant_id' => $this->tenant->id,
        'scenario' => 'decline',
    ]);
});

test('sendEmail does nothing without generated email', function () {
    Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::Decline->value,
    ])
        ->set('selectedItems', ['no_availability'])
        ->set('generatedEmail', '')
        ->call('sendEmail')
        ->assertSet('emailSent', false);

    Mail::assertNothingSent();
});

test('regenerate clears generated email', function () {
    config(['follow_up.ai.provider' => 'nonexistent']);

    $component = Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::Decline->value,
    ])
        ->set('selectedItems', ['no_availability'])
        ->call('generateEmail')
        ->assertSet('generatedEmail', fn ($val) => ! empty($val));

    $firstEmail = $component->get('generatedEmail');

    $component
        ->call('clearGenerated')
        ->assertSet('generatedEmail', '')
        ->assertSet('showPreview', false)
        ->call('generateEmail')
        ->assertSet('generatedEmail', fn ($val) => ! empty($val));
});

test('toggleItem adds and removes items', function () {
    $component = Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::Decline->value,
    ]);

    $component->call('toggleItem', 'no_availability');
    expect($component->get('selectedItems'))->toContain('no_availability');

    $component->call('toggleItem', 'no_availability');
    expect($component->get('selectedItems'))->not->toContain('no_availability');
});

test('showPreview is true after generate', function () {
    config(['follow_up.ai.provider' => 'nonexistent']);

    Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::Decline->value,
    ])
        ->set('selectedItems', ['no_availability'])
        ->assertSet('showPreview', false)
        ->call('generateEmail')
        ->assertSet('showPreview', true);
});

test('request info scenario shows info fields', function () {
    Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::RequestInfo->value,
    ])
        ->assertSee('Informação necessária')
        ->assertSee('Fotos do local')
        ->assertSee('Morada exata');
});

test('general scenario has no required items', function () {
    config(['follow_up.ai.provider' => 'nonexistent']);

    Livewire::test(FollowUpComposer::class, [
        'lead' => $this->lead,
        'scenario' => FollowUpScenario::General->value,
    ])
        ->set('selectedItems', [])
        ->set('freeText', 'Agradecimento e follow-up.')
        ->call('generateEmail')
        ->assertSet('generatedEmail', fn ($val) => ! empty($val));
});
