<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }
}
