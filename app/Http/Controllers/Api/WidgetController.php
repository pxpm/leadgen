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
            'greeting' => __('app.widget_api.default_greeting'),
            'services' => $services,
            'field_definitions' => [],
            'turnstile_site_key' => config('services.turnstile.site_key'),
        ]);
    }

    /**
     * Start a new conversation. Creates a lead and returns session_token.
     */
    public function startConversation(Tenant $tenant): JsonResponse
    {
        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'status' => LeadStatus::New,
            'source' => LeadSource::Widget,
            'session_token' => Str::random(64),
            'token_expires_at' => now()->addHours(Lead::TOKEN_TTL_HOURS),
        ]);

        // Attach all tenant industries — lead can later narrow to specific ones
        $lead->industries()->sync($tenant->industries->pluck('id'));

        return response()->json([
            'lead' => ['id' => $lead->id, 'session_token' => $lead->session_token],
        ], 201);
    }

    /**
     * Resume an existing conversation via session token.
     */
    public function resumeConversation(Lead $lead): JsonResponse
    {
        if ($lead->isTokenExpired()) {
            return response()->json([
                'error' => 'session_expired',
                'message' => __('app.widget_api.session_expired'),
            ], 410);
        }

        if ($lead->status === LeadStatus::Delivered) {
            return response()->json([
                'error' => 'session_expired',
                'message' => __('app.widget_api.session_expired'),
            ], 410);
        }

        $messages = $lead->messages()->orderBy('created_at')->get()
            ->map(fn ($msg) => ['role' => $msg->role->value, 'content' => $msg->content]);

        $fields = $lead->fields->mapWithKeys(fn ($f) => [$f->field_key => $f->field_value]);

        $response = [
            'lead' => ['id' => $lead->id, 'status' => $lead->status->value, 'source' => $lead->source->value],
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
                    'welcome_message' => $missedCallConfig['welcome_message'] ?? __('app.widget_api.missed_call_welcome'),
                    'intents' => $missedCallConfig['intents'] ?? [
                        'budget' => __('app.widget_api.intent_quote'),
                        'report' => __('app.widget_api.intent_report'),
                        'other' => __('app.widget_api.intent_other'),
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

        // Eager-load all relationships the orchestrator needs — eliminates 30+ N+1 queries per message
        $lead->loadMissing(['tenant', 'fields', 'leadServices.fields', 'messages']);

        if ($lead->isTokenExpired()) {
            return response()->json([
                'error' => 'session_expired',
                'message' => __('app.widget_api.session_expired'),
            ], 410);
        }

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

        // Extend token on every message — keeps active conversations alive
        $lead->extendToken();

        $orchestrator = app(ConversationOrchestrator::class);
        $serviceKeys = $request->input('service_keys', []);
        $result = $orchestrator->process($lead, $request->input('message'), $serviceKeys);

        return response()->json($result);
    }

    /**
     * Upload a file (photo or document) for the current conversation.
     */
    public function upload(Lead $lead, Request $request): JsonResponse
    {
        $collection = $request->input('collection', 'photos');

        // Validate collection name
        if (! in_array($collection, ['photos', 'documents'], true)) {
            return response()->json([
                'error' => 'invalid_collection',
                'message' => __('app.widget_api.invalid_collection'),
            ], 422);
        }

        // Validate file extension per collection
        $allowedExtensions = $collection === 'photos'
            ? ['jpg', 'jpeg', 'png', 'webp']
            : ['pdf', 'docx', 'xlsx'];

        $request->validate(['file' => 'required|file|max:10240']);

        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions, true)) {
            return response()->json([
                'error' => 'invalid_file_type',
                'message' => __('app.widget_api.invalid_file_type', ['types' => implode(', ', $allowedExtensions)]),
            ], 422);
        }

        if ($lead->isTokenExpired()) {
            return response()->json([
                'error' => 'session_expired',
                'message' => __('app.widget_api.session_expired'),
            ], 410);
        }

        $media = $lead->addMediaFromRequest('file')->toMediaCollection($collection);

        return response()->json([
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->file_name,
            'collection' => $collection,
        ], 201);
    }
}
