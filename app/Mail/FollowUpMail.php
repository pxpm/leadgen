<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Lead;
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
    ) {}

    public function envelope(): Envelope
    {
        $recipient = $this->lead->tenant->notification_config['email']['recipients'][0]
            ?? config('mail.from.address')
            ?? __('emails.generic.fallback_reply_to');

        return new Envelope(
            subject: $this->emailSubject,
            replyTo: [$recipient],
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: nl2br(e($this->emailBody)),
        );
    }
}
