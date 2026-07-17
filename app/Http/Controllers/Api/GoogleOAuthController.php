<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantEmailAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class GoogleOAuthController extends Controller
{
    /**
     * Redirect the tenant to Google's OAuth consent screen.
     * GET /api/oauth/google/redirect
     */
    public function redirect(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        if (! $tenant) {
            abort(403, 'No tenant associated with your account.');
        }

        // Generate a random nonce to prevent CSRF on the OAuth callback.
        // Socialite handles state internally, but we also store it for double verification.
        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        return Socialite::driver('google')
            ->scopes([
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/gmail.send',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'state' => $state,
            ])
            ->redirect();
    }

    /**
     * Handle the OAuth callback from Google.
     * GET /api/oauth/google/callback
     */
    public function callback(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        if (! $tenant) {
            abort(403, 'No tenant associated with your account.');
        }

        // Validate CSRF state nonce
        $expectedState = session()->pull('google_oauth_state');
        if (! $expectedState || ! hash_equals($expectedState, (string) $request->get('state'))) {
            Log::warning('Google OAuth: invalid state parameter — possible CSRF');

            return redirect()->to('/manage-backoffice')->with('error', 'Falha na validação de segurança. Tenta novamente.');
        }

        if ($request->has('error')) {
            Log::error('Google OAuth denied', ['error' => $request->get('error')]);

            return redirect()->to('/manage-backoffice')->with('error', 'Autorização Google cancelada.');
        }

        try {
            $socialiteUser = Socialite::driver('google')->user();

            // Store or update the tenant email account with OAuth tokens
            $account = TenantEmailAccount::firstOrNew([
                'tenant_id' => $tenant->id,
                'email' => $socialiteUser->getEmail(),
            ]);

            $account->fill([
                'provider' => 'google',
                'connection_type' => 'google_oauth',
                'name' => $socialiteUser->getName() ?? $socialiteUser->getEmail(),
                'access_token' => Crypt::encryptString($socialiteUser->token),
                'refresh_token' => $socialiteUser->refreshToken
                    ? Crypt::encryptString($socialiteUser->refreshToken)
                    : $account->refresh_token,
                'token_metadata' => [
                    'scopes' => $socialiteUser->approvedScopes,
                    'expires_at' => $socialiteUser->expiresIn
                        ? now()->addSeconds($socialiteUser->expiresIn)->toIso8601String()
                        : null,
                ],
                'imap_config' => TenantEmailAccount::defaultImapConfig('google'),
                'smtp_config' => TenantEmailAccount::defaultSmtpConfig('google'),
                'status' => 'active',
            ]);

            $account->save();

            Log::info('Google OAuth connected', [
                'tenant_id' => $tenant->id,
                'email' => $socialiteUser->getEmail(),
            ]);

            return redirect()->to('/manage-backoffice')->with('success', 'Conta Google conectada com sucesso!');
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()->to('/manage-backoffice')->with('error', 'Erro ao conectar conta Google.');
        }
    }
}
