<?php

declare(strict_types=1);

use App\Models\MissedCall;
use App\Models\Tenant;
use App\Services\MagicLinkService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

Route::get('/', fn () => view('landing.home'));

// Widget JS (served at fixed path, resolves via Vite manifest)
Route::get('/js/widget.js', function () {
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);

    $widgetFile = collect($manifest)->first(fn ($v) => str_contains($v['src'] ?? '', 'widget/main.js'));

    if (! $widgetFile) {
        abort(404);
    }

    return redirect('/build/'.$widgetFile['file']);
});

// ─── Local-only test routes ─────────────────────────────────────
if (app()->environment('local')) {
    // Widget test page
    Route::get('/widget-test', fn () => view('widget-test'));

    // Simulate a missed call for testing the full intake flow
    Route::get('/call-test', function () {
        $tenant = Tenant::first();
        if (! $tenant) {
            return response('No tenant found. Seed the database first.', 500);
        }

        $callerNumber = request('caller', '+351912345678');
        $toNumber = request('to', $tenant->twilio_phone_number ?? '+351210000001');

        $missedCall = MissedCall::create([
            'tenant_id' => $tenant->id,
            'caller_number' => $callerNumber,
            'tenant_phone' => $toNumber,
            'twilio_call_sid' => 'TEST-'.Str::random(16),
            'matched_by' => 'test',
        ]);

        $intakeUrl = URL::temporarySignedRoute(
            'missed-call.intake',
            now()->addHours(48),
            ['missedCall' => $missedCall->id]
        );

        if ($intent = request('intent')) {
            $intakeUrl .= '&intent='.$intent;
        }

        return redirect($intakeUrl);
    });
}

// ─── Production routes ───────────────────────────────────────────

// Missed call landing page
Route::get('/missed-call/{missedCall}', function (MissedCall $missedCall) {
    $tenant = $missedCall->tenant;

    return view('missed-call-landing', [
        'tenantName' => $tenant?->name ?? 'Empresa',
        'tenantSlug' => $tenant?->slug ?? '',
        'primaryColor' => $tenant?->branding_config['primary_color'] ?? '#2563eb',
        'token' => session('lgw_token_'.$missedCall->id),
        'intent' => request('intent'),
    ]);
})->name('missed-call.landing');

// Missed call full-screen widget page
Route::get('/missed-call/{missedCall}/widget', function (MissedCall $missedCall) {
    $tenant = $missedCall->tenant;

    return view('missed-call-landing', [
        'tenantName' => $tenant?->name ?? 'Empresa',
        'tenantSlug' => $tenant?->slug ?? '',
        'primaryColor' => $tenant?->branding_config['primary_color'] ?? '#2563eb',
        'token' => session('lgw_token_'.$missedCall->id),
        'intent' => request('intent'),
    ]);
})->name('missed-call.widget');

// SMS sent confirmation page (after tenant triggers the follow-up)
Route::get('/missed-call/{missedCall}/sent', function (MissedCall $missedCall) {
    return view('missed-call-sent', [
        'tenantName' => $missedCall->tenant?->name ?? 'Empresa',
    ]);
})->name('missed-call.sent');

// Magic link auth
Route::get('/magic-link/{token}', function (string $token) {
    $service = app(MagicLinkService::class);
    $user = $service->consume($token);

    if (! $user) {
        return response('Link inválido ou expirado.', 403);
    }

    auth()->login($user);

    return redirect($service->getRedirectUrl($token) ?? '/admin');
})->name('magic-link');
