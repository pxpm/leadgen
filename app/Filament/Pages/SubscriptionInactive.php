<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SubscriptionInactive extends Page
{
    protected static ?string $title = 'Subscrição Inativa';

    protected static ?string $slug = 'subscription-inactive';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.subscription-inactive';
}
