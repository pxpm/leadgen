<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\TenantEmailAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TenantEmailAccount $account,
        public string $verificationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifica o teu email — Lead Intake Assistant',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.email-verification-code',
            with: [
                'account' => $this->account,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }
}
