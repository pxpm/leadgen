<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantEmailAccount;
use Illuminate\Support\Facades\Crypt;
use IMAP\Connection;

class ImapService
{
    private ?array $currentConfig = null;

    /**
     * Connect to an IMAP server using the account's config.
     * Returns an IMAP resource or throws on failure.
     *
     * @return Connection|resource
     */
    public function connect(TenantEmailAccount $account): mixed
    {
        $config = $account->imap_config;
        $this->currentConfig = $config;
        $password = Crypt::decryptString($account->app_password);
        $host = $config['host'];
        $port = $config['port'];
        $encryption = $config['encryption'] ?? 'ssl';

        $mailbox = '{'.$host.':'.$port.'/imap/'.$encryption.'/novalidate-cert}INBOX';

        $connection = @\imap_open($mailbox, $account->email, $password, 0, 1, [
            'DISABLE_AUTHENTICATOR' => 'PLAIN',
        ]);

        if (! $connection) {
            throw new \RuntimeException('IMAP connection failed: '.\imap_last_error());
        }

        return $connection;
    }

    /**
     * Test whether the account can connect via IMAP.
     */
    public function testConnection(TenantEmailAccount $account): bool
    {
        try {
            $conn = $this->connect($account);
            \imap_close($conn);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Fetch new messages from the currently selected folder by UID range.
     * Does NOT filter by SEEN/UNSEEN — a message is "new" if its UID is
     * higher than our last processed UID, regardless of read status.
     * This ensures we catch emails that the tenant already read in their client.
     *
     * On first sync ($lastUid null), only fetches from the last 7 days to
     * avoid processing thousands of historical emails.
     *
     * @param  \\IMAP\\Connection|resource  $connection
     * @return array<int, array{uid: int, subject: string, from_address: string, from_name: ?string, to: array, cc: array, body_text: ?string, headers: array, received_at: string, message_id: ?string, in_reply_to: ?string, references: ?string}>
     */
    public function fetchNew(mixed $connection, ?int $lastUid): array
    {
        if ($lastUid !== null) {
            // Incremental: all messages with UID higher than last processed
            // UID search returns ALL messages regardless of SEEN flag
            $uids = \imap_search($connection, 'UID '.($lastUid + 1).':*');
        } else {
            // First sync: last 7 days, all messages regardless of read status
            $since = now()->subDays(7)->format('j-M-Y');
            $uids = \imap_search($connection, 'SINCE "'.$since.'"');
        }

        if (! $uids || ! is_array($uids)) {
            return [];
        }

        $messages = [];
        foreach ($uids as $uid) {
            $uid = (int) \str_replace('UID ', '', (string) \imap_uid($connection, (int) $uid));
            if (! $uid || ($lastUid !== null && $uid <= $lastUid)) {
                continue;
            }

            $message = $this->fetchMessage($connection, $uid);
            if ($message) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * Fetch a single message by UID.
     *
     * @param  Connection|resource  $connection
     * @return array{uid: int, subject: string, from_address: string, from_name: ?string, to: array, cc: array, body_text: ?string, headers: array, received_at: string, message_id: ?string, in_reply_to: ?string, references: ?string}|null
     */
    public function fetchMessage(mixed $connection, int $uid): ?array
    {
        $headerInfo = @\imap_headerinfo($connection, \imap_msgno($connection, $uid));
        if (! $headerInfo) {
            return null;
        }

        $bodyText = $this->getBody($connection, $uid, 1.0);
        // Never fetch HTML body for inbound — security risk (tracking pixels),
        // wastes AI tokens, and makes parsing harder. Text only.

        $rawHeaders = [];
        if (isset($headerInfo->from)) {
            foreach ($headerInfo->from as $from) {
                $rawHeaders['from'][] = ['mailbox' => $from->mailbox ?? '', 'host' => $from->host ?? ''];
            }
        }
        if (isset($headerInfo->to)) {
            foreach ($headerInfo->to as $to) {
                $rawHeaders['to'][] = ['mailbox' => $to->mailbox ?? '', 'host' => $to->host ?? ''];
            }
        }

        $fromAddress = '';
        $fromName = null;
        if (isset($headerInfo->from[0])) {
            $fromAddress = ($headerInfo->from[0]->mailbox ?? '').'@'.($headerInfo->from[0]->host ?? '');
            $fromName = $headerInfo->from[0]->personal ?? null;
            if ($fromName) {
                $fromName = \mb_decode_mimeheader($fromName);
            }
        }

        $toAddresses = [];
        if (isset($headerInfo->to)) {
            foreach ($headerInfo->to as $to) {
                $toAddresses[] = ($to->mailbox ?? '').'@'.($to->host ?? '');
            }
        }

        $ccAddresses = [];
        if (isset($headerInfo->cc)) {
            foreach ($headerInfo->cc as $cc) {
                $ccAddresses[] = ($cc->mailbox ?? '').'@'.($cc->host ?? '');
            }
        }

        return [
            'uid' => $uid,
            'subject' => \mb_decode_mimeheader($headerInfo->subject ?? '(sem assunto)'),
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'to' => $toAddresses,
            'cc' => $ccAddresses,
            'body_text' => $bodyText,
            'headers' => $rawHeaders,
            'received_at' => date('Y-m-d H:i:s', $headerInfo->udate ?? time()),
            'message_id' => $headerInfo->message_id ?? null,
            'in_reply_to' => $headerInfo->in_reply_to ?? null,
            'references' => $headerInfo->references ?? null,
        ];
    }

    /**
     * Get the body of a message part by section.
     *
     * @param  Connection|resource  $connection
     */
    private function getBody(mixed $connection, int $uid, float $section): ?string
    {
        $body = @\imap_fetchbody($connection, $uid, (string) $section, FT_UID | FT_PEEK);
        if (! $body || ! is_string($body)) {
            return null;
        }

        // Decode quoted-printable or base64
        $encoding = $this->getBodyEncoding($connection, $uid, $section);

        return match ($encoding) {
            3 => \base64_decode($body, true) ?: $body,     // BASE64
            4 => \quoted_printable_decode($body),            // QUOTED-PRINTABLE
            default => $body,
        };
    }

    /**
     * Get the transfer encoding of a body section.
     *
     * @param  Connection|resource  $connection
     */
    private function getBodyEncoding(mixed $connection, int $uid, float $section): int
    {
        $structure = @\imap_fetchstructure($connection, $uid, FT_UID);
        if (! $structure) {
            return 0;
        }

        // Navigate to the right part
        $part = $structure;
        if ($section === 1.1 && isset($part->parts[0])) {
            $part = $part->parts[0];
        } elseif ($section === 1.2 && isset($part->parts[1])) {
            $part = $part->parts[1];
        }

        return $part->encoding ?? 0;
    }

    /**
     * Select a specific IMAP folder.
     *
     * @param  Connection|resource  $connection
     */
    public function selectFolder(mixed $connection, string $folder): void
    {
        $config = $this->currentConfig ?? ['host' => '', 'port' => 993, 'encryption' => 'ssl'];
        $mailbox = '{'.$config['host'].':'.$config['port'].'/imap/'.$config['encryption'].'/novalidate-cert}'.$folder;

        @\imap_reopen($connection, $mailbox);
    }

    /**
     * Get the highest UID in the mailbox.
     *
     * @param  Connection|resource  $connection
     */
    public function getMaxUid(mixed $connection): int
    {
        $result = \imap_search($connection, 'ALL');
        if (! $result) {
            return 0;
        }

        $maxNo = (int) \max($result);

        return (int) \imap_uid($connection, $maxNo);
    }
}
