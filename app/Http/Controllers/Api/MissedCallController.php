<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendCallerSmsJob;
use App\Models\Lead;
use App\Models\MissedCall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MissedCallController extends Controller
{
    /**
     * Handle missed call intake via signed URL.
     * Creates a lead if needed and redirects to the full-screen widget page.
     */
    public function intake(MissedCall $missedCall, Request $request): RedirectResponse
    {
        // Intake is only accessible via short link resolution (IntakeController::resolve).
        // The short link provides its own 48h expiry — no additional signature needed.

        $intent = $request->query('intent');

        if ($missedCall->lead_id) {
            $lead = $missedCall->lead;
        } else {
            $lead = Lead::create([
                'tenant_id' => $missedCall->tenant_id,
                'industry_id' => $missedCall->tenant->industry_id,
                'status' => LeadStatus::New,
                'source' => LeadSource::MissedCall,
                'session_token' => Str::random(64),
                'token_expires_at' => now()->addHours(Lead::TOKEN_TTL_HOURS),
            ]);

            $missedCall->update([
                'lead_id' => $lead->id,
                'intent' => $intent,
            ]);
        }

        // Store token in session to avoid leaking it in URL query params
        session(['lgw_token_'.$missedCall->id => $lead->session_token]);

        return redirect('/missed-call/'.$missedCall->id.'/widget'.($intent ? '?intent='.$intent : ''));
    }

    /**
     * Tenant action: trigger SMS to the caller.
     */
    public function sendSms(MissedCall $missedCall, Request $request): RedirectResponse
    {
        // Accessible via short link resolution — short link provides its own expiry.

        SendCallerSmsJob::dispatch($missedCall);

        return redirect('/missed-call/'.$missedCall->id.'/sent');
    }
}
