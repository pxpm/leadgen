<?php

use App\Http\Controllers\Api\GoogleOAuthController;
use App\Http\Controllers\Api\InboundEmailController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WidgetController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Widget API — public, rate-limited, no auth, subscription-gated
Route::prefix('widget')->middleware(['throttle:60,1', 'active-subscription'])->group(function () {
    Route::get('/{tenant:slug}/config', [WidgetController::class, 'config']);
    Route::post('/{tenant:slug}/conversations', [WidgetController::class, 'startConversation'])
        ->middleware(['throttle:10,1', 'turnstile']);
    Route::get('/conversations/{lead:session_token}', [WidgetController::class, 'resumeConversation'])
        ->middleware('throttle:30,1');
    Route::post('/conversations/{lead:session_token}/messages', [WidgetController::class, 'sendMessage'])
        ->middleware(['throttle:30,1', 'turnstile']);
    Route::post('/conversations/{lead:session_token}/uploads', [WidgetController::class, 'upload'])
        ->middleware('turnstile');
});

// Twilio webhooks — signature-validated, rate-limited
Route::post('/webhooks/twilio/incoming-call', [WebhookController::class, 'incomingCall'])
    ->middleware(['twilio-webhook', 'throttle:30,1']);

// Stripe webhooks
Route::post('/webhooks/stripe', StripeWebhookController::class);

// Inbound email webhook (Resend, Mailgun, etc.)
Route::post('/webhooks/inbound-email', InboundEmailController::class)
    ->middleware('throttle:60,1');

// Google OAuth for email sending (authenticated, tenant-scoped)
Route::middleware(['auth'])->group(function () {
    Route::get('/oauth/google/redirect', [GoogleOAuthController::class, 'redirect']);
    Route::get('/oauth/google/callback', [GoogleOAuthController::class, 'callback']);
});

// Shareable intake link generation (authenticated, tenant-scoped)
Route::middleware(['auth', 'active-subscription'])->group(function () {
    Route::get('/intake/{tenant:slug}/generate-url', [IntakeController::class, 'generateUrl']);
});
