<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DemoRequest;
use App\Models\Industry;
use App\Models\Plan;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrialSignupController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Honeypot — bots fill hidden fields. Ban the IP, return fake success.
        if (! empty($request->input('website'))) {
            $ip = $request->ip();
            Cache::put("banned_ip:{$ip}", true, now()->addDays(30));

            return response()->json(['ok' => true], 201);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['required', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:50', function (string $attr, mixed $value, \Closure $fail) {
                if ($value !== 'outro' && ! Industry::where('slug', $value)->exists()) {
                    $fail(__('validation.exists', ['attribute' => $attr]));
                }
            }],
            'industry_other' => ['required_if:industry,outro', 'string', 'max:100'],
        ]);

        // Manual email uniqueness check — avoids account enumeration.
        if (User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => __('landing.demo_form.error_message'),
            ], 422);
        }

        // "Outro" → save as demo request for manual review. No tenant created.
        if ($validated['industry'] === 'outro') {
            DemoRequest::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company' => $validated['company'],
                'industry' => $validated['industry_other'],
                'message' => 'Pedido de trial — indústria não listada: '.$validated['industry_other'],
                'status' => 'new',
            ]);

            return response()->json(['ok' => true, 'outro' => true], 201);
        }

        $industry = Industry::where('slug', $validated['industry'])->firstOrFail();
        $trialPlan = Plan::where('slug', 'trial')->firstOrFail();

        $slug = Str::slug($validated['company']);
        $baseSlug = $slug;
        $counter = 1;
        while (\App\Models\Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        app(TenantService::class)->createTenant([
            'name' => $validated['company'],
            'slug' => $slug,
            'locale' => 'pt',
            'industry_id' => $industry->id,
            'admin_name' => $validated['name'],
            'admin_email' => $validated['email'],
            'plan_id' => $trialPlan->id,
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
            'send_magic_link' => true,
        ]);

        return response()->json(['ok' => true], 201);
    }
}
