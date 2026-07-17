<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\TenantEmailAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Refreshes OAuth access tokens for Google and Microsoft accounts
 * before they expire. Runs every 10 minutes via scheduler.
 */
class RefreshOAuthTokensJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $accounts = TenantEmailAccount::whereIn('connection_type', ['google_oauth', 'microsoft_oauth'])
            ->where('status', 'active')
            ->get();

        foreach ($accounts as $account) {
            try {
                $this->refreshIfNeeded($account);
            } catch (\Throwable $e) {
                Log::error('OAuth token refresh failed', [
                    'account_id' => $account->id,
                    'email' => $account->email,
                    'provider' => $account->provider,
                    'error' => $e->getMessage(),
                ]);

                $this->incrementFailureCount($account, $e->getMessage());
            }
        }
    }

    private function refreshIfNeeded(TenantEmailAccount $account): void
    {
        $metadata = $account->token_metadata ?? [];
        $expiresAt = $metadata['expires_at'] ?? null;

        // Refresh if token expires in less than 10 minutes
        if ($expiresAt && now()->addMinutes(10)->lt($expiresAt)) {
            return;
        }

        $refreshToken = $account->refresh_token
            ? Crypt::decryptString($account->refresh_token)
            : null;

        if (! $refreshToken) {
            Log::warning('No refresh token available', [
                'account_id' => $account->id,
                'email' => $account->email,
            ]);

            return;
        }

        $newToken = match ($account->provider) {
            'google' => $this->refreshGoogleToken($refreshToken),
            'microsoft' => $this->refreshMicrosoftToken($refreshToken),
            default => null,
        };

        if (! $newToken) {
            return;
        }

        // Permanent failure — token revoked or account deleted
        if ($this->isPermanentTokenError($newToken)) {
            $this->markAccountFailed($account, $newToken['error'] ?? 'permanent_token_error');

            return;
        }

        // Success — reset failure counter
        $account->update([
            'access_token' => Crypt::encryptString(json_encode($newToken)),
            'token_metadata' => array_merge($metadata, [
                'expires_at' => isset($newToken['expires_in'])
                    ? now()->addSeconds($newToken['expires_in'])->toIso8601String()
                    : $expiresAt,
                'consecutive_refresh_failures' => 0,
            ]),
            'last_error' => null,
            'status' => 'active',
        ]);

        Log::info('OAuth token refreshed', [
            'account_id' => $account->id,
            'email' => $account->email,
            'provider' => $account->provider,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function refreshGoogleToken(string $refreshToken): ?array
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        $body = $response->json();

        if ($response->failed()) {
            Log::error('Google token refresh failed', ['body' => $body]);

            // Return the error body so the caller can check for permanent failures
            return $body;
        }

        return $body;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function refreshMicrosoftToken(string $refreshToken): ?array
    {
        $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'client_id' => config('services.microsoft.client_id'),
            'client_secret' => config('services.microsoft.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        $body = $response->json();

        if ($response->failed()) {
            Log::error('Microsoft token refresh failed', ['body' => $body]);

            return $body;
        }

        return $body;
    }

    /**
     * Check if the token error is permanent (not retryable).
     * Google: invalid_grant = token revoked/expired
     * Microsoft: invalid_grant = token revoked/expired
     */
    private function isPermanentTokenError(array $response): bool
    {
        $error = $response['error'] ?? null;

        if (! $error) {
            return false;
        }

        // Permanent errors — token was revoked, account was deleted, or app was uninstalled
        $permanentErrors = [
            'invalid_grant',           // both Google & Microsoft
            'unauthorized_client',     // Google
            'invalid_client',          // Microsoft
        ];

        return in_array($error, $permanentErrors, true);
    }

    /**
     * Mark an account as permanently failed after token revocation.
     */
    private function markAccountFailed(TenantEmailAccount $account, string $error): void
    {
        $account->update([
            'status' => 'error',
            'last_error' => "OAuth token permanently invalid: {$error}. Reconnect the account.",
        ]);

        Log::warning('OAuth account marked as failed — token permanently invalid', [
            'account_id' => $account->id,
            'email' => $account->email,
            'provider' => $account->provider,
            'error' => $error,
        ]);
    }

    /**
     * Track consecutive refresh failures for an account.
     * After 3 consecutive failures, mark the account as failed.
     */
    private function incrementFailureCount(TenantEmailAccount $account, string $errorMessage): void
    {
        $metadata = $account->token_metadata ?? [];
        $failures = ($metadata['consecutive_refresh_failures'] ?? 0) + 1;

        if ($failures >= 3) {
            $account->update([
                'status' => 'error',
                'last_error' => "OAuth refresh failed {$failures} consecutive times. Last error: {$errorMessage}",
            ]);

            Log::warning('OAuth account marked as failed after consecutive refresh failures', [
                'account_id' => $account->id,
                'email' => $account->email,
                'provider' => $account->provider,
                'consecutive_failures' => $failures,
            ]);
        } else {
            $account->update([
                'last_error' => $errorMessage,
                'token_metadata' => array_merge($metadata, [
                    'consecutive_refresh_failures' => $failures,
                ]),
            ]);
        }
    }
}
