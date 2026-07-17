<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DownloadEmailAttachmentsJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, array{id: string, filename: string, content_type: string, download_url: ?string}>  $attachments
     */
    public function __construct(
        private Lead $lead,
        private array $attachments,
        private string $resendEmailId,
    ) {}

    public function handle(): void
    {
        $apiKey = config('services.resend.key');

        if (! $apiKey) {
            Log::channel('email_webhook')->warning('RESEND_API_KEY not configured — cannot download attachments');

            return;
        }

        $resend = \Resend::client($apiKey);
        $downloadedIds = [];

        foreach ($this->attachments as $attachment) {
            try {
                $attachmentId = $attachment['id'];
                $filename = $attachment['filename'];

                Log::channel('email_webhook')->info('📎 Downloading attachment', [
                    'lead_id' => $this->lead->id,
                    'attachment_id' => $attachmentId,
                    'filename' => $filename,
                    'content_type' => $attachment['content_type'],
                ]);

                // Fetch the attachment content from Resend API
                $attachmentData = $resend->emails->receiving->attachments->get(
                    $this->resendEmailId,
                    $attachmentId
                );

                // The SDK returns the raw content; write to temp and add to media library
                $tempPath = sys_get_temp_dir().'/resend_att_'.$attachmentId;
                file_put_contents($tempPath, $attachmentData);

                // Add to Lead's media library — stored under {tenant_id}/{media_id}/filename
                $media = $this->lead
                    ->addMedia($tempPath)
                    ->usingFileName($filename)
                    ->withCustomProperties([
                        'resend_attachment_id' => $attachmentId,
                        'resend_email_id' => $this->resendEmailId,
                        'content_type' => $attachment['content_type'],
                    ])
                    ->toMediaCollection('email-attachments');

                // Clean up temp file
                @unlink($tempPath);

                $downloadedIds[] = $attachmentId;

                Log::channel('email_webhook')->info('✅ Attachment stored', [
                    'lead_id' => $this->lead->id,
                    'media_id' => $media->id,
                    'filename' => $filename,
                    'size' => $media->size,
                    'tenant_id' => $this->lead->tenant_id,
                ]);
            } catch (\Throwable $e) {
                Log::channel('email_webhook')->error('Failed to download attachment', [
                    'lead_id' => $this->lead->id,
                    'attachment_id' => $attachment['id'],
                    'filename' => $attachment['filename'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update the lead's email message with the downloaded media IDs
        if ($downloadedIds) {
            $message = $this->lead->emailMessages()
                ->where('message_id_header', $this->resendEmailId) // not quite right — use the actual message_id
                ->latest()
                ->first();

            // Fallback: find the most recent message for this lead
            if (! $message) {
                $message = $this->lead->emailMessages()->latest()->first();
            }

            if ($message) {
                $existingMediaIds = $message->attachment_media_ids ?? [];
                // Get media IDs from the lead's email-attachments collection
                $newMedia = $this->lead->getMedia('email-attachments')
                    ->whereIn('custom_properties->resend_attachment_id', $downloadedIds);

                $allMediaIds = array_merge(
                    $existingMediaIds,
                    $newMedia->pluck('id')->toArray()
                );

                $message->update(['attachment_media_ids' => $allMediaIds]);
            }
        }
    }
}
