<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentLeadsTable extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()
                    ->where('tenant_id', auth()->user()?->tenant_id)
                    ->latest()
            )
            ->heading(__('admin.leads_table.heading'))
            ->description(__('admin.leads_table.description'))
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.leads_table.column_hash'))
                    ->sortable(),

                TextColumn::make('services')
                    ->label(__('admin.lead.column_service'))
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $service = is_array($state) ? ($state[0] ?? null) : $state;

                        return match ($service) {
                            'roofing' => __('admin.lead.service_roofing'),
                            'waterproofing' => __('admin.lead.service_waterproofing'),
                            'painting' => __('admin.lead.service_painting'),
                            'insulation' => __('admin.lead.service_insulation'),
                            'facades' => __('admin.lead.service_facades'),
                            'terraces' => __('admin.lead.service_terraces'),
                            'gutters' => __('admin.lead.service_gutters'),
                            'remodeling' => __('admin.lead.service_remodeling'),
                            default => $service ?? '—',
                        };
                    }),

                TextColumn::make('contact_name')
                    ->label(__('admin.leads_table.column_name'))
                    ->state(fn (Lead $record) => $record->fields()->where('field_key', 'contact_name')->value('field_value') ?? '—')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('admin.leads_table.column_phone'))
                    ->state(fn (Lead $record) => $record->fields()->where('field_key', 'phone')->value('field_value') ?? '—'),

                TextColumn::make('email')
                    ->label(__('admin.leads_table.column_email'))
                    ->state(fn (Lead $record) => $record->fields()->where('field_key', 'email')->value('field_value') ?? '—'),

                TextColumn::make('status')
                    ->label(__('admin.leads_table.column_status'))
                    ->badge()
                    ->color(fn (LeadStatus $state) => match ($state) {
                        LeadStatus::New => 'gray',
                        LeadStatus::InProgress => 'warning',
                        LeadStatus::Qualified => 'success',
                        LeadStatus::Delivered => 'info',
                        LeadStatus::Archived => 'danger',
                    })
                    ->formatStateUsing(fn (LeadStatus $state) => __('admin.lead.status_'.$state->value)),

                TextColumn::make('source')
                    ->label(__('admin.leads_table.column_source'))
                    ->badge(),

                TextColumn::make('created_at')
                    ->label(__('admin.leads_table.column_date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('admin.leads_table.action_view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Lead $record) => LeadResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Allow super-admin when impersonating a tenant
        if ($user->isSuperAdmin() && request()->cookie('impersonating_tenant_id')) {
            return true;
        }

        return ! $user->isSuperAdmin();
    }
}
