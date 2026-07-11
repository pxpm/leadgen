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
                    ->limit(15)
            )
            ->heading('Leads Recentes')
            ->description('Últimos 15 leads recebidos')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Serviço')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'roofing' => '🏠 Telhados',
                        'waterproofing' => '💧 Impermeabilização',
                        'painting' => '🎨 Pintura',
                        'insulation' => '🔧 Isolamento',
                        'facades' => '🏢 Fachadas',
                        'terraces' => '🌿 Terraços',
                        'gutters' => '🏚️ Algerozes',
                        'remodeling' => '🔨 Remodelação',
                        default => $state ?? '—',
                    }),

                TextColumn::make('contact_name')
                    ->label('Nome')
                    ->state(fn (Lead $record) => $record->fields()->where('field_key', 'contact_name')->value('field_value') ?? '—')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Telefone')
                    ->state(fn (Lead $record) => $record->fields()->where('field_key', 'phone')->value('field_value') ?? '—'),

                TextColumn::make('email')
                    ->label('Email')
                    ->state(fn (Lead $record) => $record->fields()->where('field_key', 'email')->value('field_value') ?? '—'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (LeadStatus $state) => match ($state) {
                        LeadStatus::New => 'gray',
                        LeadStatus::InProgress => 'warning',
                        LeadStatus::Qualified => 'success',
                        LeadStatus::Delivered => 'info',
                        LeadStatus::Archived => 'danger',
                    }),

                TextColumn::make('source')
                    ->label('Origem')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Lead $record) => LeadResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return ! auth()->user()?->isSuperAdmin();
    }
}
