<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantEmailAccount;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSendService
{
    private const GMAIL_SEND_URL = 'https://gmail.googleapis.com/upload/gmail/v1/users/me/messages/send';

    /**
     * Send an email via the Gmail API using the tenant's OAuth token.
     *
     * @return string The Gmail message ID if successful.
     */
    public function send(TenantEmailAccount $account, string $to, string $subject, string $bodyText, ?string $bodyHtml = null, ?string $inReplyTo = null, ?string $references = null): string
    {
        $token = $this->resolveAccessToken($account);

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

        // Gmail API raw format: URL-safe base64 without padding
        $encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($rawMessage));

        $response = Http::withToken($token)
            ->withBody(json_encode(['raw' => $encoded]), 'application/json')
            ->post(self::GMAIL_SEND_URL);

        if ($response->failed()) {
            Log::error('Gmail API send failed', [
                'account_id' => $account->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Gmail API send failed: '.$response->status());
        }

        $messageId = $response->json('id') ?? 'sent';

        Log::info('Email sent via Gmail API', [
            'account_id' => $account->id,
            'to' => $to,
            'gmail_message_id' => $messageId,
        ]);

        return $messageId;
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

        return $token['access_token'] ?? (is_string($token) ? Crypt::decryptString($account->access_token) : '');
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
