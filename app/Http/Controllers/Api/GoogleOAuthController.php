<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantEmailAccount;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Oauth2;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

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

        $client = $this->createClient();

        $authUrl = $client->createAuthUrl();

        return redirect($authUrl);
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

        if ($request->has('error')) {
            Log::error('Google OAuth denied', ['error' => $request->get('error')]);

            return redirect()->to('/manage-backoffice')->with('error', 'Autorização Google cancelada.');
        }

        try {
            $client = $this->createClient();
            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));

            if (isset($token['error'])) {
                Log::error('Google OAuth token exchange failed', ['error' => $token['error']]);

                return redirect()->to('/manage-backoffice')->with('error', 'Falha na autorização Google.');
            }

            // Get user info to determine email
            $client->setAccessToken($token);
            $oauth2 = new Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            // Store or update the tenant email account with OAuth tokens
            $account = TenantEmailAccount::firstOrNew([
                'tenant_id' => $tenant->id,
                'email' => $userInfo->email,
            ]);

            $account->fill([
                'provider' => 'google',
                'connection_type' => 'google_oauth',
                'name' => $userInfo->name ?? $userInfo->email,
                'access_token' => Crypt::encryptString(json_encode($token)),
                'refresh_token' => isset($token['refresh_token']) ? Crypt::encryptString($token['refresh_token']) : $account->refresh_token,
                'token_metadata' => [
                    'scopes' => $token['scope'] ?? '',
                    'expires_at' => isset($token['expires_in']) ? now()->addSeconds($token['expires_in'])->toIso8601String() : null,
                ],
                'imap_config' => TenantEmailAccount::defaultImapConfig('google'),
                'smtp_config' => TenantEmailAccount::defaultSmtpConfig('google'),
                'status' => 'active',
            ]);

            $account->save();

            Log::info('Google OAuth connected', [
                'tenant_id' => $tenant->id,
                'email' => $userInfo->email,
            ]);

            return redirect()->to('/manage-backoffice')->with('success', 'Conta Google conectada com sucesso!');
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()->to('/manage-backoffice')->with('error', 'Erro ao conectar conta Google.');
        }
    }

    private function createClient(): Client
    {
        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);
        $client->addScope([
            'openid',
            'profile',
            'email',
            Gmail::GMAIL_SEND,
        ]);

        return $client;
    }
}
