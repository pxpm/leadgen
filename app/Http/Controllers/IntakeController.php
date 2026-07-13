<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Jobs\SendCallerSmsJob;
use App\Models\Lead;
use App\Models\MissedCall;
use App\Models\ShortLink;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IntakeController extends Controller
{
    /**
     * Resolve a short link hash and route to the correct handler.
     * GET /s/{shortLink:hash}
     */
    public function resolve(ShortLink $shortLink, Request $request): RedirectResponse
    {
        if ($shortLink->isExpired()) {
            abort(410, 'Este link expirou.');
        }

        return match ($shortLink->source) {
            'direct_link' => $this->resolveDirectLink($shortLink, $request),
            'missed_call_intake' => $this->resolveMissedCallIntake($shortLink, $request),
            'missed_call_send_sms' => $this->resolveMissedCallSendSms($shortLink),
            default => abort(400, 'Tipo de link desconhecido.'),
        };
    }

    /**
     * Direct link: create a lead and redirect to the intake widget.
     */
    private function resolveDirectLink(ShortLink $shortLink, Request $request): RedirectResponse
    {
        $tenant = $shortLink->tenant;
        $intent = $request->query('intent')
            ?? $shortLink->metadata['intent'] ?? null;

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'industry_id' => $tenant->industry_id,
            'status' => LeadStatus::New,
            'source' => LeadSource::DirectLink,
            'session_token' => Str::random(64),
        ]);

        $shortLink->update(['lead_id' => $lead->id]);

        session(['lgw_intake_'.$tenant->id => $lead->session_token]);

        return redirect()->route('intake.widget', [
            'tenant' => $tenant->slug,
            'intent' => $intent,
        ]);
    }

    /**
     * Missed call intake: create a lead (if needed) and redirect to the missed-call widget.
     */
    private function resolveMissedCallIntake(ShortLink $shortLink, Request $request): RedirectResponse
    {
        $missedCallId = $shortLink->metadata['missed_call_id'] ?? null;
        $missedCall = MissedCall::findOrFail($missedCallId);
        $intent = $request->query('intent')
            ?? $shortLink->metadata['intent'] ?? null;

        if ($missedCall->lead_id) {
            $lead = $missedCall->lead;
        } else {
            $lead = Lead::create([
                'tenant_id' => $missedCall->tenant_id,
                'industry_id' => $missedCall->tenant->industry_id,
                'status' => LeadStatus::New,
                'source' => LeadSource::MissedCall,
                'session_token' => Str::random(64),
            ]);

            $missedCall->update([
                'lead_id' => $lead->id,
                'intent' => $intent,
            ]);
        }

        $shortLink->update(['lead_id' => $lead->id]);

        session(['lgw_token_'.$missedCall->id => $lead->session_token]);

        return redirect('/missed-call/'.$missedCall->id.'/widget'.($intent ? '?intent='.$intent : ''));
    }

    /**
     * Missed call send-sms: dispatch the caller SMS job and redirect to the sent page.
     */
    private function resolveMissedCallSendSms(ShortLink $shortLink): RedirectResponse
    {
        $missedCallId = $shortLink->metadata['missed_call_id'] ?? null;
        $missedCall = MissedCall::findOrFail($missedCallId);

        SendCallerSmsJob::dispatch($missedCall);

        return redirect('/missed-call/'.$missedCall->id.'/sent');
    }

    /**
     * Render the full-screen widget page for direct intake links.
     * Only accessible after going through the short link (resolve) which
     * sets the session token for this tenant.
     */
    public function widget(Tenant $tenant, Request $request): View
    {
        $token = session('lgw_intake_'.$tenant->id);

        if (! $token) {
            abort(403, 'Link inválido ou expirado.');
        }

        return view('intake-landing', [
            'tenantName' => $tenant->name,
            'tenantSlug' => $tenant->slug,
            'primaryColor' => $tenant->branding_config['primary_color'] ?? '#2563eb',
            'token' => $token,
            'intent' => $request->query('intent'),
        ]);
    }

    /**
     * Generate a shareable short intake URL for a tenant.
     * Used by admin panel actions or API calls.
     */
    public function generateUrl(Tenant $tenant): JsonResponse
    {
        $shortLink = ShortLink::forDirectLink($tenant);

        return response()->json([
            'url' => url('/s/'.$shortLink->hash),
            'expires_at' => $shortLink->expires_at->toIso8601String(),
        ]);
    }
}
