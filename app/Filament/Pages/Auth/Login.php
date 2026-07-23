<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Mail\MagicLinkLoginRequest;
use App\Services\MagicLinkService;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public bool $magicLinkSent = false;

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getSubHeading(): string|Htmlable|null
    {
        return null;
    }

    public function hasLogo(): bool
    {
        return false;
    }

    /**
     * Send a magic link to the email address in the form.
     */
    public function sendMagicLink(): void
    {
        $email = $this->data['email'] ?? null;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'data.email' => __('validation.email', ['attribute' => 'email']),
            ]);
        }

        // Rate limit: 1 magic link per email+IP per 60 seconds
        $rateLimitKey = 'magic-link:' . request()->ip() . ':' . $email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 1)) {
            // Silently succeed — don't reveal rate limiting to attackers
            $this->magicLinkSent = true;

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        $user = \App\Models\User::where('email', $email)->first();

        if (! $user) {
            // Don't reveal whether the email exists — show success anyway
            $this->magicLinkSent = true;

            return;
        }

        $service = app(MagicLinkService::class);
        $url = $service->createForFirstLogin($user);

        Mail::to($user)->queue(new MagicLinkLoginRequest($url, $user->name));

        $this->magicLinkSent = true;
    }

    /**
     * Get the Google social auth redirect URL.
     */
    public function getGoogleLoginUrl(): string
    {
        return route('social.redirect', ['provider' => 'google', 'intent' => 'login']);
    }

    /**
     * Get the Facebook social auth redirect URL.
     */
    public function getFacebookLoginUrl(): string
    {
        return route('social.redirect', ['provider' => 'facebook', 'intent' => 'login']);
    }
}
