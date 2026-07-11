<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\HandleIncomingCallJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    /**
     * Handle Twilio incoming call webhook.
     */
    public function incomingCall(Request $request): Response
    {
        // TODO: Validate Twilio signature

        HandleIncomingCallJob::dispatch(
            callerNumber: $request->input('From'),
            toNumber: $request->input('To'),
            callSid: $request->input('CallSid'),
            forwardedFrom: $request->input('ForwardedFrom'),
        );

        return response()->noContent();
    }
}
