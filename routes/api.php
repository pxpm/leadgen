<?php

use App\Http\Controllers\Api\MissedCallController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WidgetController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Widget API — public, rate-limited, no auth, subscription-gated
Route::prefix('widget')->middleware(['throttle:60,1', 'active-subscription'])->group(function () {
    Route::get('/{tenant:slug}/config', [WidgetController::class, 'config']);
    Route::post('/{tenant:slug}/conversations', [WidgetController::class, 'startConversation'])
        ->middleware('throttle:10,1');
    Route::get('/conversations/{lead:session_token}', [WidgetController::class, 'resumeConversation']);
    Route::post('/conversations/{lead:session_token}/messages', [WidgetController::class, 'sendMessage'])
        ->middleware(['throttle:30,1', 'turnstile']);
    Route::post('/conversations/{lead:session_token}/uploads', [WidgetController::class, 'upload'])
        ->middleware('turnstile');
});

// Twilio webhooks
Route::post('/webhooks/twilio/incoming-call', [WebhookController::class, 'incomingCall']);

// Stripe webhooks
Route::post('/webhooks/stripe', StripeWebhookController::class);

// Missed call intake (signed URL)
Route::get('/missed-calls/{missedCall}/intake', [MissedCallController::class, 'intake'])
    ->name('missed-call.intake');
Route::get('/missed-calls/{missedCall}/send-sms', [MissedCallController::class, 'sendSms'])
    ->name('missed-call.send-sms');
