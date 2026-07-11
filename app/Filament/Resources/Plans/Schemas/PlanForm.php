<?php

declare(strict_types=1);

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informação do Plano')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->columns(2),

                Section::make('Limites Mensais')
                    ->schema([
                        TextInput::make('limits.sms_monthly')
                            ->label('SMS')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(100),

                        TextInput::make('limits.email_monthly')
                            ->label('Emails')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(500),

                        TextInput::make('limits.ai_ingestion_monthly')
                            ->label('AI Ingestion')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(50),
                    ])
                    ->columns(3),

                Section::make('Visibilidade')
                    ->schema([
                        Toggle::make('is_public')
                            ->label('Plano Público')
                            ->helperText('Visível na página de preços para self-serve.')
                            ->default(true),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->helperText('Planos inativos não podem ser atribuídos.')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Ordem')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(3),
            ]);
    }
}
