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
     * Request signature is validated by ValidateTwilioWebhook middleware.
     */
    public function incomingCall(Request $request): Response
    {
        HandleIncomingCallJob::dispatch(
            callerNumber: $request->input('From'),
            toNumber: $request->input('To'),
            callSid: $request->input('CallSid'),
            forwardedFrom: $request->input('ForwardedFrom'),
        );

        return response()->noContent();
    }
}
