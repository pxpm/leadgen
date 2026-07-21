<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Blocks requests from IPs banned via honeypot traps.
 * Returns a fake 200 to avoid tipping off bots.
 */
class BlockBannedIps
{
    public function handle(Request $request, Closure $next): mixed
    {
        $ip = $request->ip();

        if (Cache::has("banned_ip:{$ip}")) {
            return response()->json(['ok' => true]);
        }

        return $next($request);
    }
}
