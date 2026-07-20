<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Lead;
use App\Models\TenantEmailAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowUpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public string $emailBody,
        public string $emailSubject,
        public ?TenantEmailAccount $fromAccount = null,
        public ?TenantEmailAccount $replyToAccount = null,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = $this->replyToAccount?->email
            ?? $this->lead->tenant->notification_config['email']['recipients'][0]
            ?? config('mail.from.address');

        $envelope = new Envelope(
            subject: $this->emailSubject,
            replyTo: [$replyTo],
            bcc: [$this->buildBccAddress()],
        );

        if ($this->fromAccount) {
            $envelope->from($this->fromAccount->email, $this->fromAccount->name ?? null);
        }

        return $envelope;
    }

    /**
     * Build the BCC address for lead tracking: lead+{tenant_slug}@{mail_domain}.
     */
    private function buildBccAddress(): string
    {
        $domain = substr(strrchr((string) config('mail.from.address'), '@'), 1);

        return 'lead+'.$this->lead->tenant->slug.'@'.$domain;
    }

    public function content(): Content
    {
        return new Content(
            htmlString: nl2br(e($this->emailBody)),
        );
    }
}
