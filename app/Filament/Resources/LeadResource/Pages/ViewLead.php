<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadResource\Pages;

use App\Enums\FollowUpScenario;
use App\Filament\Resources\LeadResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('decline_lead')
                ->label('Rejeitar Lead')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Rejeitar Lead')
                ->modalDescription('Selecione o(s) motivo(s) para rejeitar este lead e gere um email profissional.')
                ->modalContent(fn () => view('livewire.follow-up-composer-wrapper', [
                    'lead' => $this->record,
                    'scenario' => FollowUpScenario::Decline->value,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fechar'),

            Action::make('request_info')
                ->label('Pedir Informações')
                ->icon('heroicon-o-question-mark-circle')
                ->color('warning')
                ->modalHeading('Pedir Informações ao Cliente')
                ->modalDescription('Selecione a informação que precisa e gere um email profissional.')
                ->modalContent(fn () => view('livewire.follow-up-composer-wrapper', [
                    'lead' => $this->record,
                    'scenario' => FollowUpScenario::RequestInfo->value,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fechar'),

            Action::make('quote_followup')
                ->label('Acompanhar Orçamento')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->modalHeading('Acompanhar Orçamento')
                ->modalDescription('Selecione o estágio do acompanhamento e gere um email profissional.')
                ->modalContent(fn () => view('livewire.follow-up-composer-wrapper', [
                    'lead' => $this->record,
                    'scenario' => FollowUpScenario::QuoteFollowUp->value,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fechar'),

            Action::make('general_contact')
                ->label('Contacto Geral')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('gray')
                ->modalHeading('Contacto Geral')
                ->modalDescription('Escreva notas e gere um email personalizado.')
                ->modalContent(fn () => view('livewire.follow-up-composer-wrapper', [
                    'lead' => $this->record,
                    'scenario' => FollowUpScenario::General->value,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fechar'),
        ];
    }
}
