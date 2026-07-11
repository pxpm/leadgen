<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MissedCall;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentMissedCallsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MissedCall::query()
                    ->where('tenant_id', auth()->user()?->tenant_id)
                    ->latest()
                    ->limit(15)
            )
            ->heading('Chamadas Perdidas Recentes')
            ->description('Últimas 15 chamadas não atendidas')
            ->columns([
                TextColumn::make('caller_number')
                    ->label('Número')
                    ->searchable(),

                TextColumn::make('tenant_phone')
                    ->label('Linha')
                    ->searchable(),

                TextColumn::make('matched_by')
                    ->label('Correspondência')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'dedicated_number' => 'Número direto',
                        'forwarded_from' => 'Reencaminhado',
                        default => $state ?? '—',
                    }),

                TextColumn::make('intent')
                    ->label('Intenção')
                    ->badge()
                    ->placeholder('—'),

                IconColumn::make('sms_sent')
                    ->label('SMS?')
                    ->boolean(),

                TextColumn::make('lead_id')
                    ->label('Lead')
                    ->state(fn (MissedCall $record) => $record->lead_id ? "#{$record->lead_id}" : '—'),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return ! auth()->user()?->isSuperAdmin();
    }
}
