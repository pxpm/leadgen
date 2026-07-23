<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DemoRequest;
use App\Models\Industry;
use App\Models\Plan;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TrialCompleteController extends Controller
{
    /**
     * Show the trial completion form (after social auth).
     */
    public function show()
    {
        $social = Session::get('social_auth');

        if (! $social) {
            return redirect('/');
        }

        $industries = Industry::where('is_active', true)->orderBy('name')->get();

        return view('trial.complete', [
            'socialName' => $social['name'],
            'socialEmail' => $social['email'],
            'socialProvider' => $social['provider'],
            'industries' => $industries,
        ]);
    }

    /**
     * Complete the trial signup after social auth.
     */
    public function store(Request $request): RedirectResponse
    {
        $social = Session::get('social_auth');

        if (! $social) {
            return redirect('/');
        }

        $validated = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'industry' => ['required', 'string', 'max:50', function (string $attr, mixed $value, \Closure $fail) {
                if ($value !== 'outro' && ! Industry::where('slug', $value)->exists()) {
                    $fail(__('validation.exists', ['attribute' => __('validation.attributes.industry')]));
                }
            }],
            'industry_other' => ['required_if:industry,outro', 'nullable', 'string', 'max:100'],
        ]);

        // Duplicate email from social auth — treat as already registered
        if (User::where('email', $social['email'])->exists()) {
            return back()->with('error', __('landing.demo_form.error_message'));
        }

        // "Outro" → demo request
        if ($validated['industry'] === 'outro') {
            DemoRequest::create([
                'name' => $social['name'],
                'email' => $social['email'],
                'phone' => $validated['phone'] ?? null,
                'company' => $validated['company'],
                'industry' => $validated['industry_other'],
                'message' => 'Pedido de trial ('.$social['provider'].') — indústria não listada: '.$validated['industry_other'],
                'status' => 'new',
            ]);

            Session::forget('social_auth');

            return redirect('/')->with('success', 'outro');
        }

        $industry = Industry::where('slug', $validated['industry'])->first();
        $trialPlan = Plan::where('slug', 'trial')->first();

        if (! $industry || ! $trialPlan) {
            return back()->with('error', __('landing.demo_form.error_message'));
        }

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
            'industries' => [$industry->id],
            'admin_name' => $social['name'],
            'admin_email' => $social['email'],
            'plan_id' => $trialPlan->id,
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
            'send_magic_link' => true,
        ]);

        Session::forget('social_auth');

        return redirect('/')->with('success', 'trial');
    }
}
