<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantEmailAccount;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class GoogleSendService
{
    /**
     * Send an email via the Gmail API using the tenant's OAuth token.
     * Falls back to SMTP if Gmail API fails.
     *
     * @return string The Gmail message ID if successful.
     */
    public function send(TenantEmailAccount $account, string $to, string $subject, string $bodyText, ?string $bodyHtml = null, ?string $inReplyTo = null, ?string $references = null): string
    {
        $client = $this->buildClient($account);

        $gmail = new Gmail($client);

        $rawMessage = $this->buildMimeMessage(
            from: $account->email,
            fromName: $account->name ?? $account->email,
            to: $to,
            subject: $subject,
            bodyText: $bodyText,
            bodyHtml: $bodyHtml,
            inReplyTo: $inReplyTo,
            references: $references,
        );

        $encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($rawMessage));

        try {
            $message = $gmail->users_messages->send('me', new Message([
                'raw' => $encoded,
            ]));

            Log::info('Email sent via Gmail API', [
                'account_id' => $account->id,
                'to' => $to,
                'gmail_message_id' => $message->getId(),
            ]);

            return $message->getId();
        } catch (\Throwable $e) {
            Log::error('Gmail API send failed, falling back to SMTP', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Let the caller handle the fallback
        }
    }

    /**
     * Build and configure the Google API client with stored tokens.
     */
    private function buildClient(TenantEmailAccount $account): Client
    {
        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessType('offline');

        // Restore stored access token
        if ($account->access_token) {
            $token = json_decode(Crypt::decryptString($account->access_token), true);
            if ($token) {
                $client->setAccessToken($token);
            }
        }

        // Hook into token refresh to persist new tokens
        $client->setTokenCallback(function ($cacheKey, $newToken) use ($account): void {
            $account->update([
                'access_token' => Crypt::encryptString(json_encode($newToken)),
                'token_metadata' => array_merge($account->token_metadata ?? [], [
                    'expires_at' => isset($newToken['expires_in'])
                        ? now()->addSeconds($newToken['expires_in'])->toIso8601String()
                        : null,
                ]),
            ]);

            Log::info('Google OAuth token refreshed', [
                'account_id' => $account->id,
            ]);
        });

        return $client;
    }

    /**
     * Build a raw MIME message for the Gmail API.
     */
    private function buildMimeMessage(
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $bodyText,
        ?string $bodyHtml = null,
        ?string $inReplyTo = null,
        ?string $references = null,
    ): string {
        $messageId = '<'.bin2hex(random_bytes(16)).'@'.$from.'>';
        $boundary = '=_'.bin2hex(random_bytes(16));

        $headers = "From: {$fromName} <{$from}>\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= 'Subject: =?UTF-8?B?'.base64_encode($subject)."?=\r\n";
        $headers .= "Message-ID: {$messageId}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

        if ($inReplyTo) {
            $headers .= "In-Reply-To: {$inReplyTo}\r\n";
        }
        if ($references) {
            $headers .= "References: {$references}\r\n";
        }

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= quoted_printable_encode($bodyText)."\r\n\r\n";

        if ($bodyHtml) {
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
            $body .= quoted_printable_encode($bodyHtml)."\r\n\r\n";
        }

        $body .= "--{$boundary}--";

        return $headers."\r\n".$body;
    }
}
