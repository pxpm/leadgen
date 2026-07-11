<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\MissedCall;
use App\Models\Tenant;
use App\Services\ConversationOrchestrator;
use App\Services\IndustryConfigEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    /**
     * Load tenant branding + available services for widget initialization.
     */
    public function config(Tenant $tenant): JsonResponse
    {
        $configEngine = app(IndustryConfigEngine::class);
        $locale = $configEngine->getLocale($tenant);
        $services = $configEngine->getAvailableServices($tenant);

        return response()->json([
            'tenant' => [
                'name' => $tenant->name,
                'logo_url' => $tenant->getFirstMediaUrl('logo') ?: null,
                'primary_color' => $tenant->branding_config['primary_color'] ?? '#1a56db',
                'locale' => $locale,
            ],
            'greeting' => 'Olá! Em que podemos ajudar?',
            'services' => $services,
            'field_definitions' => [],
        ]);
    }

    /**
     * Start a new conversation. Creates a lead and returns session_token.
     */
    public function startConversation(Tenant $tenant): JsonResponse
    {
        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'industry_id' => $tenant->industry_id,
            'status' => LeadStatus::New,
            'source' => LeadSource::Widget,
            'session_token' => Str::random(64),
        ]);

        return response()->json([
            'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token],
        ], 201);
    }

    /**
     * Resume an existing conversation via session token.
     */
    public function resumeConversation(Lead $lead): JsonResponse
    {
        if ($lead->status === LeadStatus::Delivered) {
            return response()->json([
                'error' => 'session_expired',
                'message' => 'Esta sessão expirou. Por favor inicie uma nova conversa.',
            ], 410);
        }

        $messages = $lead->messages()->orderBy('created_at')->get()
            ->map(fn ($msg) => ['role' => $msg->role->value, 'content' => $msg->content]);

        $fields = $lead->fields->mapWithKeys(fn ($f) => [$f->field_key => $f->field_value]);

        $response = [
            'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token, 'status' => $lead->status->value, 'source' => $lead->source->value],
            'messages' => $messages,
            'collected_fields' => $fields,
        ];

        // If this is a missed call lead with no messages yet, include intent selection data
        if ($lead->source === LeadSource::MissedCall && $messages->isEmpty()) {
            $config = app(IndustryConfigEngine::class)->resolve($lead->tenant);
            $locale = app(IndustryConfigEngine::class)->getLocale($lead->tenant);
            $missedCallConfig = $config['locales'][$locale]['missed_call'] ?? null;

            if ($missedCallConfig) {
                $response['intent_selection'] = [
                    'welcome_message' => $missedCallConfig['welcome_message'] ?? 'Olá! Como podemos ajudar?',
                    'intents' => $missedCallConfig['intents'] ?? [
                        'budget' => 'Quero um orçamento',
                        'report' => 'Reportar um problema',
                        'other' => 'Outro assunto',
                    ],
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * Send a message in an active conversation.
     * AI orchestration is queued — returns placeholder for now.
     */
    public function sendMessage(Lead $lead, Request $request): JsonResponse
    {
        $request->validate(['message' => 'required|string|max:2000']);

        if ($lead->status === LeadStatus::Delivered) {
            return response()->json(['error' => 'session_expired', 'message' => 'Esta sessão expirou.'], 410);
        }

        if ($lead->status === LeadStatus::New) {
            $lead->update(['status' => LeadStatus::InProgress, 'conversation_started_at' => now()]);
        }

        // Handle intent selection for missed call leads
        $intent = $request->input('intent');
        if ($intent && $lead->source === LeadSource::MissedCall) {
            $missedCall = MissedCall::where('lead_id', $lead->id)->first();
            if ($missedCall) {
                $missedCall->update(['intent' => $intent]);
            }
        }

        $lead->messages()->create(['role' => 'user', 'content' => $request->input('message')]);

        $orchestrator = app(ConversationOrchestrator::class);
        $serviceKeys = $request->input('service_keys', []);
        $result = $orchestrator->process($lead, $request->input('message'), $serviceKeys);

        return response()->json($result);
    }

    /**
     * Upload a photo for the current conversation.
     */
    public function upload(Lead $lead, Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|image|max:10240']);

        $media = $lead->addMediaFromRequest('file')->toMediaCollection('photos');

        return response()->json([
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->file_name,
        ], 201);
    }

    /**
     * Build localized field definitions with prompts and option labels.
     */
    private function localizedFields(array $config, string $locale): array
    {
        $definitions = $config['field_definitions'] ?? [];
        $prompts = $config['locales'][$locale]['field_prompts'] ?? [];
        $options = $config['locales'][$locale]['field_options'] ?? [];

        return collect($definitions)->map(function ($def, $key) use ($prompts, $options) {
            $entry = ['key' => $key, 'type' => $def['type'], 'prompt' => $prompts[$key] ?? null];

            if ($def['type'] === 'select' && isset($options[$key])) {
                $entry['options'] = collect($def['options'])
                    ->map(fn ($val) => ['value' => $val, 'label' => $options[$key][$val] ?? $val])
                    ->values()->toArray();
            }

            return $entry;
        })->values()->toArray();
    }
}
