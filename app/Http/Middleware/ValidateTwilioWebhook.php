<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateTwilioWebhook
{
    /**
     * Validate that the incoming request is genuinely from Twilio.
     *
     * Twilio signs requests with HMAC-SHA1 using the auth token as the key.
     * The signature is sent in the X-Twilio-Signature header.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = config('services.twilio.token');

        if (empty($token)) {
            // Not configured — skip validation (dev mode)
            return $next($request);
        }

        $signature = $request->header('X-Twilio-Signature');

        if (empty($signature)) {
            return response()->json(['message' => 'Missing signature.'], 403);
        }

        $url = $request->fullUrl();
        $params = $request->post();
        ksort($params);

        $data = $url;
        foreach ($params as $key => $value) {
            $data .= $key.$value;
        }

        $expected = base64_encode(hash_hmac('sha1', $data, $token, true));

        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        return $next($request);
    }
}
