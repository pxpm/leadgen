<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\TranslationService;
use Illuminate\Database\Seeder;

class TranslationDefaultSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(TranslationService::class);

        // Seed from lang files
        foreach (['pt'] as $locale) {
            $path = lang_path("{$locale}/orchestrator.php");

            if (file_exists($path)) {
                $translations = require $path;
                $service->seedFromFile($locale, 'orchestrator', $translations);
            }
        }
    }
}
