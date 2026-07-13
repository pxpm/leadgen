<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadQualifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        $name = $this->lead->fields->where('field_key', 'contact_name')->first()?->field_value ?? 'Novo Lead';

        return new Envelope(
            subject: __('emails.lead_qualified.subject', ['name' => $name]),
            replyTo: [$this->lead->tenant->notification_config['email']['recipients'][0] ?? __('emails.generic.fallback_reply_to')],
        );
    }

    public function content(): Content
    {
        $fields = $this->lead->fields->pluck('field_value', 'field_key');
        $photos = $this->lead->getMedia('photos')->map->getUrl()->toArray();

        return new Content(
            text: 'emails.lead-qualified',
            with: [
                'lead' => $this->lead,
                'fields' => $fields,
                'photos' => $photos,
                'score' => $this->lead->qualification_score,
            ],
        );
    }
}
