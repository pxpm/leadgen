<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subject,
        public string $bodyText,
        public ?string $bodyHtml = null,
        public ?string $fromAddress = null,
        public ?string $fromName = null,
        public ?string $messageId = null,
        public ?string $inReplyTo = null,
        public ?string $references = null,
    ) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->subject,
        );

        if ($this->fromAddress) {
            $envelope->from($this->fromAddress, $this->fromName ?? $this->fromAddress);
        }

        if ($this->messageId) {
            $envelope->withSymfonyMessage(function ($message) {
                $headers = $message->getHeaders();
                $headers->addTextHeader('Message-ID', $this->messageId);
                if ($this->inReplyTo) {
                    $headers->addTextHeader('In-Reply-To', $this->inReplyTo);
                }
                if ($this->references) {
                    $headers->addTextHeader('References', $this->references);
                }
            });
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->bodyHtml ?? nl2br(e($this->bodyText)),
        );
    }
}
