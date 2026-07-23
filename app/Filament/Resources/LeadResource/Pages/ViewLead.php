<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadResource\Pages;

use App\Enums\FollowUpScenario;
use App\Filament\Resources\LeadResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make($this->emailActions())
                ->label(__('admin.lead_actions.email_followup'))
                ->icon('heroicon-o-envelope')
                ->iconPosition('before')
                ->color('primary')
                ->button()
                ->dropdownPlacement('bottom-end'),
        ];
    }

    /** @return array<Action> */
    private function emailActions(): array
    {
        return [
            Action::make('decline_lead')
                ->label(__('admin.lead_actions.decline'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading(__('admin.lead_actions.decline_heading'))
                ->modalDescription(__('admin.lead_actions.decline_description'))
                ->modalContent(fn () => view('livewire.follow-up-composer-wrapper', [
                    'lead' => $this->record,
                    'scenario' => FollowUpScenario::Decline->value,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(__('admin.common.close')),

            Action::make('request_info')
                ->label(__('admin.lead_actions.request_info'))
                ->icon('heroicon-o-question-mark-circle')
                ->color('warning')
                ->modalHeading(__('admin.lead_actions.request_info_heading'))
                ->modalDescription(__('admin.lead_actions.request_info_description'))
                ->modalContent(fn () => view('livewire.follow-up-composer-wrapper', [
                    'lead' => $this->record,
                    'scenario' => FollowUpScenario::RequestInfo->value,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(__('admin.common.close')),

            Action::make('quote_followup')
                ->label(__('admin.lead_actions.quote_followup'))
                ->icon('heroicon-o-clock')
                ->color('info')
                ->modalHeading(__('admin.lead_actions.quote_followup_heading'))
                ->modalDescription(__('admin.lead_actions.quote_followup_description'))
                ->modalContent(fn () => view('livewire.follow-up-composer-wrapper', [
                    'lead' => $this->record,
                    'scenario' => FollowUpScenario::QuoteFollowUp->value,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(__('admin.common.close')),

            Action::make('general_contact')
                ->label(__('admin.lead_actions.general_contact'))
                ->icon('heroicon-o-chat-bubble-left')
                ->color('gray')
                ->modalHeading(__('admin.lead_actions.general_contact_heading'))
                ->modalDescription(__('admin.lead_actions.general_contact_description'))
                ->modalContent(fn () => view('livewire.follow-up-composer-wrapper', [
                    'lead' => $this->record,
                    'scenario' => FollowUpScenario::General->value,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(__('admin.common.close')),
        ];
    }
}
