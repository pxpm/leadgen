<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadResource\RelationManagers;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'emailMessages';

    protected static ?string $title = 'Emails';

    protected static BackedEnum|string|null $icon = Heroicon::OutlinedEnvelope;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('direction')
                    ->label('')
                    ->formatStateUsing(fn (string $state): string => $state === 'inbound' ? '📥' : '📤')
                    ->width('40px'),

                TextColumn::make('from_address')
                    ->label('De / Para')
                    ->formatStateUsing(function ($record) {
                        if ($record->direction === 'inbound') {
                            return $record->from_name
                                ? "{$record->from_name} <{$record->from_address}>"
                                : $record->from_address;
                        }

                        $to = $record->to_addresses ?? [];

                        return 'Para: '.implode(', ', $to);
                    })
                    ->searchable(['from_address', 'from_name']),

                TextColumn::make('subject')
                    ->label('Assunto')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('body_text')
                    ->label('Pré-visualização')
                    ->limit(100)
                    ->html(),

                TextColumn::make('received_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('received_at', 'asc')
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn ($record) => $record->subject ?? '(sem assunto)'),
            ]);
    }
}
