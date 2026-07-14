<?php

declare(strict_types=1);

use App\Http\Controllers\Api\MissedCallController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\IntakeController;
use App\Models\MissedCall;
use App\Models\ShortLink;
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

        $shortLink = ShortLink::forMissedCallIntake($missedCall);
        $url = '/s/'.$shortLink->hash;

        $intent = request('intent');
        if ($intent) {
            $url .= '?intent='.$intent;
        }

        return redirect($url);
    });

    // Test the direct intake (shareable link) flow
    Route::get('/intake-test', function () {
        $tenant = Tenant::first();
        if (! $tenant) {
            return response('No tenant found. Seed the database first.', 500);
        }

        $shortLink = ShortLink::forDirectLink($tenant);

        $url = '/s/'.$shortLink->hash;

        $intent = request('intent');
        if ($intent) {
            $url .= '?intent='.$intent;
        }

        return redirect($url);
    });
}

// ─── Production routes ───────────────────────────────────────────

// Missed call landing page
Route::get('/missed-call/{missedCall}', function (MissedCall $missedCall) {
    $token = session('lgw_token_'.$missedCall->id);
    if (! $token) {
        abort(403, 'Link inválido ou expirado.');
    }

    $tenant = $missedCall->tenant;

    return view('missed-call-landing', [
        'tenantName' => $tenant?->name ?? 'Empresa',
        'tenantSlug' => $tenant?->slug ?? '',
        'primaryColor' => $tenant?->branding_config['primary_color'] ?? '#2563eb',
        'token' => $token,
        'intent' => request('intent'),
    ]);
})->name('missed-call.landing');

// Missed call full-screen widget page
Route::get('/missed-call/{missedCall}/widget', function (MissedCall $missedCall) {
    $token = session('lgw_token_'.$missedCall->id);
    if (! $token) {
        abort(403, 'Link inválido ou expirado.');
    }

    $tenant = $missedCall->tenant;

    return view('missed-call-landing', [
        'tenantName' => $tenant?->name ?? 'Empresa',
        'tenantSlug' => $tenant?->slug ?? '',
        'primaryColor' => $tenant?->branding_config['primary_color'] ?? '#2563eb',
        'token' => $token,
        'intent' => request('intent'),
    ]);
})->name('missed-call.widget');

// SMS sent confirmation page (after tenant triggers the follow-up)
Route::get('/missed-call/{missedCall}/sent', function (MissedCall $missedCall) {
    return view('missed-call-sent', [
        'tenantName' => $missedCall->tenant?->name ?? 'Empresa',
    ]);
})->name('missed-call.sent');

// Missed call intake (signed URL — needs session middleware for token handoff)
Route::get('/missed-calls/{missedCall}/intake', [MissedCallController::class, 'intake'])
    ->name('missed-call.intake');
Route::get('/missed-calls/{missedCall}/send-sms', [MissedCallController::class, 'sendSms'])
    ->name('missed-call.send-sms');

// Short link resolver (public, no auth — like bit.ly but for intake)
Route::get('/s/{shortLink:hash}', [IntakeController::class, 'resolve'])
    ->name('short-link.resolve');

// Direct intake (shareable link — short link based, 24h expiry)
Route::get('/intake/{tenant:slug}/widget', [IntakeController::class, 'widget'])
    ->name('intake.widget');

// Magic link auth
Route::get('/magic-link/{token}', function (string $token) {
    $service = app(MagicLinkService::class);
    $user = $service->consume($token);

    if (! $user) {
        return response('Link inválido ou expirado.', 403);
    }

    auth()->login($user);

    return redirect($service->getRedirectUrl($token) ?? '/manage-backoffice');
})->name('magic-link');

// ─── SEO Landing Page Routes (must be last — catch-all patterns) ───

// Generic single-segment pages: How It Works + Industries overview (locale-aware)
Route::get('/{pageSlug}', function (string $pageSlug) {
    // Check if it's the How It Works page slug
    $howItWorksSlugs = collect(['pt', 'en'])->map(fn ($l) => __('landing.how_it_works_slug', [], $l));
    if ($howItWorksSlugs->contains($pageSlug)) {
        return view('landing.how-it-works');
    }

    // Check if it's the Industries overview page slug
    $industriesSlugs = collect(['pt', 'en'])->map(fn ($l) => __('landing.industrias_slug', [], $l));
    if ($industriesSlugs->contains($pageSlug)) {
        return view('landing.industries');
    }

    abort(404);
})->where('pageSlug', '[a-z-]+')->name('landing.page');

// Industry-specific SEO landing pages — locale-aware prefix & slug
Route::get('/{prefix}/{slug}', function (string $prefix, string $slug) {
    $validPrefixes = collect(['pt', 'en'])->map(fn ($l) => __('landing.route_prefix', [], $l));
    if (! $validPrefixes->contains($prefix)) {
        abort(404);
    }

    // Search across all locales to find the industry by slug
    $industryKey = null;
    foreach (['pt', 'en'] as $locale) {
        $industries = __('landing.industries_section', [], $locale);
        $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);
        foreach ($trades as $key => $trade) {
            if (($trade['slug'] ?? '') === $slug) {
                $industryKey = $key;
                break 2;
            }
        }
    }

    if (! $industryKey || ! isset(__('landing.industry_pages.'.$industryKey)['hero_headline'])) {
        abort(404);
    }

    return view('landing.industry', ['industryKey' => $industryKey]);
})->where(['prefix' => '[a-z-]+', 'slug' => '[a-z-]+'])->name('landing.industry');

// Service-specific SEO landing pages (e.g. /solucoes-para/telhados/reparacao-telhados)
Route::get('/{prefix}/{industry}/{service}', function (string $prefix, string $industry, string $service) {
    $validPrefixes = collect(['pt', 'en'])->map(fn ($l) => __('landing.route_prefix', [], $l));
    if (! $validPrefixes->contains($prefix)) {
        abort(404);
    }

    // Search across all locales to find the industry by slug
    $industryKey = null;
    foreach (['pt', 'en'] as $locale) {
        $industries = __('landing.industries_section', [], $locale);
        $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);
        foreach ($trades as $key => $trade) {
            if (($trade['slug'] ?? '') === $industry) {
                $industryKey = $key;
                break 2;
            }
        }
    }

    if (! $industryKey) {
        abort(404);
    }

    // Search across all locales for the service slug
    $serviceData = null;
    foreach (['pt', 'en'] as $locale) {
        $services = __('landing.industry_pages.'.$industryKey.'.services', [], $locale) ?? [];
        $found = collect($services)->first(fn ($s) => ($s['slug'] ?? '') === $service);
        if ($found) {
            $serviceData = $found;
            break;
        }
    }

    if (! $serviceData) {
        abort(404);
    }

    return view('landing.service', [
        'industryKey' => $industryKey,
        'serviceData' => $serviceData,
    ]);
})->where(['prefix' => '[a-z-]+', 'industry' => '[a-z-]+', 'service' => '[a-z-]+'])->name('landing.service');

// Sitemap XML
Route::get('/sitemap.xml', function () {
    $industries = __('landing.industries_section');
    $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);

    $urls = [
        ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => how_it_works_url(), 'priority' => '0.9', 'changefreq' => 'monthly'],
        ['loc' => industries_url(), 'priority' => '0.9', 'changefreq' => 'weekly'],
    ];

    foreach ($trades as $key => $trade) {
        $slug = $trade['slug'] ?? '';
        if ($slug) {
            $urls[] = [
                'loc' => industry_url($key),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }

        // Service sub-pages
        $services = __('landing.industry_pages.'.$key.'.services') ?? [];
        foreach ($services as $svc) {
            $svcSlug = $svc['slug'] ?? '';
            if ($svcSlug) {
                $urls[] = [
                    'loc' => service_url($key, $svcSlug),
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                ];
            }
        }
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $url) {
        $xml .= '  <url>'."\n";
        $xml .= '    <loc>'.e($url['loc']).'</loc>'."\n";
        $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
        $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
        $xml .= '  </url>'."\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

// Demo request form submission
Route::post('/demo-request', [DemoRequestController::class, 'store'])->name('demo.request');
