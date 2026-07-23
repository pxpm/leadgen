<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ImpersonationController extends Controller
{
    public function start(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = $request->user();

        if (! $user?->isSuperAdmin()) {
            abort(403);
        }

        $cookie = Cookie::make(
            'impersonating_tenant_id',
            (string) $tenant->id,
            minutes: 0, // session cookie — expires when browser closes
            httpOnly: true,
        );

        return redirect()->to('/manage-backoffice/tenant-dashboard')->withCookie($cookie);
    }

    public function stop(): RedirectResponse
    {
        $cookie = Cookie::forget('impersonating_tenant_id');

        return redirect()->to('/manage-backoffice')->withCookie($cookie);
    }
}
