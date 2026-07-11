<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\RelationManagers;

use App\Enums\SubscriptionStatus;
use App\Filament\Resources\TenantResource\Pages\EditTenant;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = 'Subscrições';

    public function getPageClass(): string
    {
        return EditTenant::class;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name', fn ($query) => $query->where('is_active', true))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('status')
                    ->label('Estado')
                    ->options(collect(SubscriptionStatus::cases())->mapWithKeys(fn (SubscriptionStatus $status) => [$status->value => $status->label()])->toArray())
                    ->required(),

                DateTimePicker::make('trial_ends_at')
                    ->label('Fim do Trial')
                    ->nullable(),

                DateTimePicker::make('ends_at')
                    ->label('Termina em')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('plan.name')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof \BackedEnum ? $state->value : (string) $state)
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : (string) $state) {
                        'active' => 'success',
                        'trialing' => 'info',
                        'past_due' => 'warning',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('trial_ends_at')
                    ->label('Fim do Trial')
                    ->dateTime('d/m/Y'),

                TextColumn::make('ends_at')
                    ->label('Termina em')
                    ->dateTime('d/m/Y'),

                TextColumn::make('created_at')
                    ->label('Criado')
                    ->dateTime('d/m/Y'),
            ])
            ->headerActions([
                CreateAction::make()->label('Nova Subscrição'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
