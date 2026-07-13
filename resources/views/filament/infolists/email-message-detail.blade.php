<div style="padding: 1rem; max-height: 70vh; overflow-y: auto;">
    <div style="margin-bottom: 1rem; color: #6b7280; font-size: 0.875rem;">
        <strong>{{ __('admin.email_messages.detail_from') }}</strong> {{ $message->from_name ?? $message->from_address }}
        &lt;{{ $message->from_address }}&gt;<br>
        <strong>{{ __('admin.email_messages.detail_to') }}</strong> {{ implode(', ', $message->to_addresses ?? []) }}<br>
        @if($message->cc_addresses)
            <strong>{{ __('admin.email_messages.detail_cc') }}</strong> {{ implode(', ', $message->cc_addresses) }}<br>
        @endif
        <strong>{{ __('admin.email_messages.detail_date') }}</strong> {{ $message->received_at?->format('d/m/Y H:i') }}
    </div>

    <hr style="margin: 1rem 0;">

    <div style="white-space: pre-wrap; word-break: break-word;">
        {{ $message->body_text }}
    </div>

    @if($message->attachment_media_ids)
        <hr style="margin: 1rem 0;">
        <strong>{{ __('admin.email_messages.detail_attachments') }}</strong>
        <ul>
            @foreach($message->attachment_media_ids as $mediaId)
                <li>📎 Anexo #{{ $mediaId }}</li>
            @endforeach
        </ul>
    @endif
</div>
