<?php

use App\Jobs\RefreshOAuthTokensJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Email integration — poll IMAP inboxes every 2 minutes
Schedule::command('email:poll')->everyTwoMinutes()->withoutOverlapping();

// OAuth token refresh — keep Google/Microsoft tokens alive
Schedule::job(new RefreshOAuthTokensJob)->everyTenMinutes();

// Trial expiry — cancel subscriptions past their trial period
Schedule::command('trials:expire')->daily();

// Horizon metrics — snapshot queue stats every 5 minutes
Schedule::command('horizon:snapshot')->everyFiveMinutes();
