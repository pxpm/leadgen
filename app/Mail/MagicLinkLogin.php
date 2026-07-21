<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLinkLogin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $magicLinkUrl,
        public string $userName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.magic_link.subject', ['name' => $this->userName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.magic-link-login',
            with: [
                'magicLinkUrl' => $this->magicLinkUrl,
                'userName' => $this->userName,
            ],
        );
    }
}
