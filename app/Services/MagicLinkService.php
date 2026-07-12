<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Support\Str;

class MagicLinkService
{
    /**
     * Generate a magic link token for a specific user to view a lead.
     * If no email is given, targets the first admin user of the tenant.
     */
    public function createForLead(Lead $lead, ?string $email = null): string
    {
        if ($email) {
            $user = $lead->tenant->users()->where('email', $email)->first();
        } else {
            $user = $lead->tenant->users()->first();
        }

        if (! $user) {
            return '';
        }

        $magicLink = MagicLink::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'redirect_to' => route('filament.admin.resources.leads.view', $lead),
            'expires_at' => now()->addDays(7),
        ]);

        return route('magic-link', $magicLink->token);
    }

    /**
     * Validate and consume a magic link token.
     */
    public function consume(string $token): ?User
    {
        $link = MagicLink::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $link) {
            return null;
        }

        $link->update(['used_at' => now()]);

        return $link->user;
    }

    public function getRedirectUrl(string $token): ?string
    {
        $link = MagicLink::where('token', $token)->first();

        return $link?->redirect_to;
    }

    /**
     * Generate a magic link for a user's first login after onboarding.
     * Redirects to the Filament dashboard where they'll be prompted to set a password.
     */
    public function createForFirstLogin(User $user): string
    {
        $magicLink = MagicLink::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'redirect_to' => url('/admin'),
            'expires_at' => now()->addDays(7),
        ]);

        return route('magic-link', $magicLink->token);
    }
}
