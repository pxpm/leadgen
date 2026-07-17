<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantEmailAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class MicrosoftOAuthController extends Controller
{
    /**
     * Redirect to Microsoft's OAuth consent screen.
     * GET /api/oauth/microsoft/redirect
     */
    public function redirect(Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);
        if (! $tenant) {
            abort(403);
        }

        // Generate a random nonce to prevent CSRF on the OAuth callback.
        $state = Str::random(40);
        session(['ms_oauth_state' => $state]);

        return Socialite::driver('microsoft')
            ->scopes([
                'openid',
                'profile',
                'email',
                'offline_access',
                'Mail.Send',
            ])
            ->with(['state' => $state])
            ->redirect();
    }

    /**
     * Handle the OAuth callback from Microsoft.
     * GET /api/oauth/microsoft/callback
     */
    public function callback(Request $request): RedirectResponse
    {
        // Validate CSRF state nonce
        $expectedState = session()->pull('ms_oauth_state');
        if (! $expectedState || ! hash_equals($expectedState, (string) $request->get('state'))) {
            Log::warning('Microsoft OAuth: invalid state parameter — possible CSRF');

            return redirect()->to('/manage-backoffice/tenant-dashboard')->with('error', 'Falha na validação de segurança. Tenta novamente.');
        }

        $tenant = $this->resolveTenant($request);
        if (! $tenant) {
            abort(403);
        }

        if ($request->has('error')) {
            Log::error('Microsoft OAuth denied', ['error' => $request->get('error')]);

            return redirect()->to('/manage-backoffice/tenant-dashboard')->with('error', 'Autorização Microsoft cancelada.');
        }

        try {
            $socialiteUser = Socialite::driver('microsoft')->user();

            // Store or update the tenant email account
            $account = TenantEmailAccount::firstOrNew([
                'tenant_id' => $tenant->id,
                'email' => $socialiteUser->getEmail(),
            ]);

            $account->fill([
                'provider' => 'microsoft',
                'connection_type' => 'microsoft_oauth',
                'name' => $socialiteUser->getName() ?? $socialiteUser->getEmail(),
                'access_token' => Crypt::encryptString($socialiteUser->token),
                'refresh_token' => $socialiteUser->refreshToken
                    ? Crypt::encryptString($socialiteUser->refreshToken)
                    : null,
                'token_metadata' => [
                    'scopes' => $socialiteUser->approvedScopes,
                    'expires_at' => $socialiteUser->expiresIn
                        ? now()->addSeconds($socialiteUser->expiresIn)->toIso8601String()
                        : null,
                ],
                'status' => 'active',
                'verified_at' => now(),
            ]);

            $account->save();

            Log::info('Microsoft OAuth connected', [
                'tenant_id' => $tenant->id,
                'email' => $socialiteUser->getEmail(),
            ]);

            return redirect()->to('/manage-backoffice/tenant-dashboard')->with('success', 'Conta Microsoft conectada com sucesso!');
        } catch (\Throwable $e) {
            Log::error('Microsoft OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()->to('/manage-backoffice/tenant-dashboard')->with('error', 'Erro ao conectar conta Microsoft.');
        }
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        $user = $request->user();

        if ($user?->isSuperAdmin()) {
            return Tenant::first();
        }

        return $user?->tenant;
    }
}
