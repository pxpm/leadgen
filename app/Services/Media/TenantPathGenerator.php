<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Tenant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Organizes all media files by tenant ID so we can:
 * 1. Track disk usage per tenant
 * 2. Use disk space as a usage/billing metric
 *
 * Path structure: {tenant_id}/{media_id}/{filename}
 */
class TenantPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->resolveTenantId($media).'/'.$media->id.'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'responsive/';
    }

    private function resolveTenantId(Media $media): string
    {
        $model = $media->model;

        // Tenant model itself
        if ($model instanceof Tenant) {
            return (string) $model->id;
        }

        // Models with tenant_id (Lead, etc.)
        if ($model && isset($model->tenant_id)) {
            return (string) $model->tenant_id;
        }

        // Fallback — should not happen in normal operation
        return '0';
    }
}
