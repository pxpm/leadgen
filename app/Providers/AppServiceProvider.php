<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\SmsProvider;
use App\Services\Media\TenantPathGenerator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsProvider::class, TwilioSmsProvider::class);

        // Tenant-scoped media storage: all files organized by tenant ID
        $this->app->bind(PathGenerator::class, TenantPathGenerator::class);
    }

    public function boot(): void
    {
        // Force all URL generation to use APP_URL (critical when behind ngrok
        // where the Host header is leadgen.test but the public URL is ngrok)
        if ($root = config('app.url')) {
            URL::forceRootUrl($root);
        }
    }
}
