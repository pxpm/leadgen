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
            )
            ->heading(__('admin.missed_calls_table.heading'))
            ->description(__('admin.missed_calls_table.description'))
            ->columns([
                TextColumn::make('caller_number')
                    ->label(__('admin.missed_calls_table.column_number'))
                    ->searchable(),

                TextColumn::make('tenant_phone')
                    ->label(__('admin.missed_calls_table.column_line'))
                    ->searchable(),

                TextColumn::make('matched_by')
                    ->label(__('admin.missed_calls_table.column_match'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'dedicated_number' => __('admin.missed_calls_table.column_match_dedicated'),
                        'forwarded_from' => __('admin.missed_calls_table.column_match_forwarded'),
                        default => $state ?? '—',
                    }),

                TextColumn::make('intent')
                    ->label(__('admin.missed_calls_table.column_intent'))
                    ->badge()
                    ->placeholder('—'),

                IconColumn::make('sms_sent')
                    ->label(__('admin.missed_calls_table.column_sms'))
                    ->boolean(),

                TextColumn::make('lead_id')
                    ->label(__('admin.missed_calls_table.column_lead'))
                    ->state(fn (MissedCall $record) => $record->lead_id ? "#{$record->lead_id}" : '—'),

                TextColumn::make('created_at')
                    ->label(__('admin.missed_calls_table.column_date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function canView(): bool
    {
        return ! auth()->user()?->isSuperAdmin();
    }
}
