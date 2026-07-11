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
        public string $tenantName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bem-vindo ao Lead Intake — {$this->tenantName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.magic-link-login',
            with: [
                'magicLinkUrl' => $this->magicLinkUrl,
                'tenantName' => $this->tenantName,
            ],
        );
    }
}
