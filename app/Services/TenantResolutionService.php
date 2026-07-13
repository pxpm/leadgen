<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantEmailAccount;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TenantResolutionService
{
    /**
     * Resolve the tenant from an inbound email's metadata.
     * Strategies are tried in order. Returns null if no tenant can be identified.
     *
     * @param  array{from: string, to: array<int, string>, cc: array<int, string>}  $envelope
     */
    public function resolve(array $envelope): ?Tenant
    {
        // 1. Plus addressing: parse +{slug} from all recipient addresses
        $tenant = $this->resolveByPlusAddressing($envelope);
        if ($tenant) {
            Log::info('Tenant resolved via plus addressing', [
                'slug' => $tenant->slug,
                'tenant_id' => $tenant->id,
            ]);

            return $tenant;
        }

        // 2. Tenant email matching: FROM address matches a connected account or user email
        $tenant = $this->resolveByTenantEmail($envelope['from'] ?? '');
        if ($tenant) {
            Log::info('Tenant resolved via email matching', [
                'email' => $envelope['from'],
                'tenant_id' => $tenant->id,
            ]);

            return $tenant;
        }

        Log::warning('Could not resolve tenant for inbound email', [
            'from' => $envelope['from'] ?? 'unknown',
            'to' => implode(', ', $envelope['to'] ?? []),
        ]);

        return null;
    }

    /**
     * Parse lead+{slug}@domain from recipient addresses.
     */
    private function resolveByPlusAddressing(array $envelope): ?Tenant
    {
        $recipients = array_merge(
            $envelope['to'] ?? [],
            $envelope['cc'] ?? []
        );

        foreach ($recipients as $address) {
            $slug = $this->extractSlug($address);
            if ($slug) {
                return Tenant::where('slug', $slug)->first();
            }
        }

        return null;
    }

    /**
     * Extract tenant slug from plus-addressed email.
     * lead+telhados-lisboa@leadgen.test → telhados-lisboa
     */
    private function extractSlug(string $email): ?string
    {
        // Match local part before @ that contains +{slug}
        if (preg_match('/^[^@]+\+([^@]+)@/', $email, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Match the FROM address to a tenant's connected email account or user login email.
     */
    private function resolveByTenantEmail(string $fromAddress): ?Tenant
    {
        if (empty($fromAddress)) {
            return null;
        }

        $fromAddress = strtolower(trim($fromAddress));

        // Check connected email accounts
        $account = TenantEmailAccount::where('email', $fromAddress)->first();
        if ($account) {
            return $account->tenant;
        }

        // Check user login emails
        $user = User::where('email', $fromAddress)->first();
        if ($user?->tenant) {
            return $user->tenant;
        }

        return null;
    }
}
