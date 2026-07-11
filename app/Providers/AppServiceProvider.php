<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\SmsProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsProvider::class, TwilioSmsProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
