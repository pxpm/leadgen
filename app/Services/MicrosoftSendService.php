<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantEmailAccount;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftSendService
{
    private const GRAPH_SEND_URL = 'https://graph.microsoft.com/v1.0/me/sendMail';

    /**
     * Send an email via Microsoft Graph API using the tenant's OAuth token.
     *
     * @return string The message ID if successful.
     */
    public function send(TenantEmailAccount $account, string $to, string $subject, string $bodyText, ?string $bodyHtml = null): string
    {
        $token = $this->resolveAccessToken($account);

        $response = Http::withToken($token)
            ->post(self::GRAPH_SEND_URL, [
                'message' => [
                    'subject' => $subject,
                    'body' => [
                        'contentType' => $bodyHtml ? 'HTML' : 'Text',
                        'content' => $bodyHtml ?? $bodyText,
                    ],
                    'toRecipients' => [
                        ['emailAddress' => ['address' => $to]],
                    ],
                ],
                'saveToSentItems' => true,
            ]);

        if ($response->failed()) {
            Log::error('Microsoft Graph send failed', [
                'account_id' => $account->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Microsoft Graph send failed: '.$response->status());
        }

        Log::info('Email sent via Microsoft Graph', [
            'account_id' => $account->id,
            'to' => $to,
        ]);

        return 'sent';
    }

    /**
     * Extract the access token from the encrypted token storage.
     */
    private function resolveAccessToken(TenantEmailAccount $account): string
    {
        if (! $account->access_token) {
            throw new \RuntimeException('No access token for account '.$account->id);
        }

        $token = json_decode(Crypt::decryptString($account->access_token), true);

        return $token['access_token'] ?? '';
    }
}
