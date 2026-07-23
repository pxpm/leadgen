<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\FollowUpScenario;
use App\Mail\FollowUpMail;
use App\Models\EmailLearningExample;
use App\Models\FollowUpAction;
use App\Models\Lead;
use App\Models\LeadEmailMessage;
use App\Models\TenantEmailAccount;
use App\Services\EmailComposer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class FollowUpComposer extends Component
{
    #[Locked]
    public Lead $lead;

    #[Locked]
    public string $scenario = '';

    /** @var array<string> */
    public array $selectedItems = [];

    /** @var array<string> */
    public array $expandedGroups = [];

    public string $freeText = '';

    public string $generatedEmail = '';

    public string $errorMessage = '';

    public bool $isGenerating = false;

    public bool $isSending = false;

    public bool $emailSent = false;

    public bool $showPreview = false;

    /** @var ?int Selected from-account ID (null = platform default) */
    public ?int $fromAccountId = null;

    /** @var ?int Selected reply-to account ID (null = notification recipient) */
    public ?int $replyToAccountId = null;

    public function mount(Lead $lead, string $scenario): void
    {
        $this->lead = $lead;
        $this->scenario = $scenario;

        $accounts = $this->getSendingAccounts();

        // Find the account that originated this lead (inbound email)
        $originatingAccountId = LeadEmailMessage::where('lead_id', $lead->id)
            ->where('direction', 'inbound')
            ->whereNotNull('tenant_email_account_id')
            ->oldest()
            ->value('tenant_email_account_id');

        if ($originatingAccountId && $accounts->contains('id', $originatingAccountId)) {
            // Lead came via this account and it's still active → pre-select it
            $this->fromAccountId = $originatingAccountId;
        } elseif ($accounts->isNotEmpty()) {
            // Lead didn't come via email, but tenant has sending accounts → pre-select first
            $this->fromAccountId = $accounts->first()->id;
        }
        // else: no sending accounts → stays null (platform default, selector hidden)

        // Reply-to always mirrors the from account by default.
        // Tenant can change it independently if they have multiple accounts.
        $this->replyToAccountId = $this->fromAccountId;
    }

    public function toggleItem(string $key): void
    {
        if (in_array($key, $this->selectedItems, true)) {
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$key]));
        } else {
            $this->selectedItems[] = $key;
        }
    }

    public function toggleGroup(string $group): void
    {
        if (in_array($group, $this->expandedGroups, true)) {
            $this->expandedGroups = array_values(array_diff($this->expandedGroups, [$group]));
        } else {
            $this->expandedGroups[] = $group;
        }
    }

    public function clearGenerated(): void
    {
        $this->generatedEmail = '';
        $this->errorMessage = '';
        $this->showPreview = false;
    }

    public function generateEmail(): void
    {
        if (empty($this->selectedItems) && $this->scenario !== FollowUpScenario::General->value) {
            return;
        }

        $this->errorMessage = '';
        $this->isGenerating = true;

        $scenario = FollowUpScenario::from($this->scenario);
        $composer = app(EmailComposer::class);

        try {
            $this->generatedEmail = $composer->compose(
                lead: $this->lead,
                scenario: $scenario,
                selectedItems: $this->selectedItems,
                freeText: $this->freeText ?: null,
            );
        } catch (\Throwable $e) {
            $this->errorMessage = __('Erro ao gerar email: ').$e->getMessage();
            $this->isGenerating = false;

            return;
        }

        $this->showPreview = ! empty($this->generatedEmail);
        $this->isGenerating = false;
    }

    public function sendEmail(): void
    {
        if (empty($this->generatedEmail)) {
            return;
        }

        $this->isSending = true;

        $recipientEmail = $this->lead->fields()->where('field_key', 'email')->first()?->field_value;

        if (! $recipientEmail) {
            $this->isSending = false;

            return;
        }

        $scenario = FollowUpScenario::from($this->scenario);
        $config = config("follow_up.scenarios.{$scenario->value}");
        $locale = $this->lead->tenant->locale ?? 'pt';
        $locales = $config['locales'][$locale] ?? $config['locales']['pt'];
        // Sanitize tenant name — strip CR/LF to prevent SMTP header injection
        $safeName = str_replace(["\r", "\n"], '', $this->lead->tenant->name);
        $subject = str_replace(':tenant', $safeName, $locales['subject']);

        $fromAccount = $this->fromAccountId
            ? TenantEmailAccount::where('id', $this->fromAccountId)
                ->where('tenant_id', $this->lead->tenant_id)
                ->first()
            : null;

        $replyToAccount = $this->replyToAccountId
            ? TenantEmailAccount::where('id', $this->replyToAccountId)
                ->where('tenant_id', $this->lead->tenant_id)
                ->first()
            : null;

        // Send email
        Mail::to($recipientEmail)->send(new FollowUpMail(
            lead: $this->lead,
            emailBody: $this->generatedEmail,
            emailSubject: $subject,
            fromAccount: $fromAccount,
            replyToAccount: $replyToAccount,
        ));

        // Record the action
        $action = FollowUpAction::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'scenario' => $scenario,
            'selected_items' => $this->selectedItems,
            'free_text' => $this->freeText ?: null,
            'generated_email' => $this->generatedEmail,
            'final_email' => $this->generatedEmail,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Record learning example for future improvement
        EmailLearningExample::recordFromAction($action);

        // Record notification for history
        $this->lead->notifications()->create([
            'tenant_id' => $this->lead->tenant_id,
            'channel' => 'email',
            'recipient' => $recipientEmail,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->emailSent = true;
        $this->isSending = false;
    }

    public function getScenarioConfigProperty(): array
    {
        return config("follow_up.scenarios.{$this->scenario}", []);
    }

    public function getHasGroupsProperty(): bool
    {
        return ! empty($this->scenarioConfig['groups']);
    }

    public function getGroupsProperty(): array
    {
        return $this->scenarioConfig['groups'] ?? [];
    }

    public function getReasonsProperty(): array
    {
        // Flat list (request_info, quote_followup) — no groups
        return $this->scenarioConfig['reasons'] ?? [];
    }

    public function getSelectedLabelsProperty(): array
    {
        $reasons = $this->reasons;
        if (empty($reasons)) {
            // Try grouped reasons
            foreach ($this->groups as $group) {
                foreach ($group['reasons'] as $key => $label) {
                    $reasons[$key] = $label;
                }
            }
        }

        return array_map(fn ($item) => $reasons[$item] ?? $item, $this->selectedItems);
    }

    /**
     * Active email accounts that can send (any verified account).
     *
     * @return Collection<int, TenantEmailAccount>
     */
    public function getAccountsProperty(): Collection
    {
        return $this->getSendingAccounts();
    }

    /**
     * Whether to show the account selector at all.
     */
    public function getHasSendingAccountsProperty(): bool
    {
        return $this->getSendingAccounts()->isNotEmpty();
    }

    /**
     * Cached list of active tenant email accounts.
     *
     * @return Collection<int, TenantEmailAccount>
     */
    private function getSendingAccounts(): Collection
    {
        return TenantEmailAccount::where('tenant_id', $this->lead->tenant_id)
            ->active()
            ->whereIn('purpose', ['sending', 'both'])
            ->orderBy('email')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.follow-up-composer');
    }
}
