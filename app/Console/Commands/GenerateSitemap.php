<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'app:generate-sitemap';

    protected $description = 'Generate a static sitemap.xml file in the public directory.';

    public function handle(): int
    {
        $lastmod = now()->toDateString();

        $industries = __('landing.industries_section');
        $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);

        $urls = [
            // Core pages
            ['loc' => url('/'), 'lastmod' => $lastmod],
            ['loc' => how_it_works_url(), 'lastmod' => $lastmod],
            ['loc' => industries_url(), 'lastmod' => $lastmod],
            ['loc' => pricing_url(), 'lastmod' => $lastmod],
            ['loc' => privacy_url(), 'lastmod' => $lastmod],
            ['loc' => terms_url(), 'lastmod' => $lastmod],
            ['loc' => contact_url(), 'lastmod' => $lastmod],
            // Blog index
            ['loc' => url('/blog'), 'lastmod' => $lastmod],
        ];

        // City pages
        foreach (['lisboa', 'porto', 'algarve', 'minho', 'alentejo'] as $city) {
            $urls[] = [
                'loc' => url('/orcamentos-'.$city),
                'lastmod' => $lastmod,
            ];
        }

        // Industry + service pages
        foreach ($trades as $key => $trade) {
            $urls[] = [
                'loc' => industry_url($key),
                'lastmod' => $lastmod,
            ];

            $services = __('landing.industry_pages.'.$key.'.services') ?? [];
            foreach ($services as $svc) {
                $svcSlug = $svc['slug'] ?? '';
                if ($svcSlug) {
                    $urls[] = [
                        'loc' => service_url($key, $svcSlug),
                        'lastmod' => $lastmod,
                    ];
                }
            }
        }

        // Blog posts
        $articles = __('landing.blog_index.articles') ?? [];
        foreach ($articles as $slug => $article) {
            $urls[] = [
                'loc' => url('/blog/'.$slug),
                'lastmod' => $lastmod,
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.e($url['loc']).'</loc>'."\n";
            $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
            $xml .= '  </url>'."\n";
        }
        $xml .= '</urlset>';

        $path = public_path('sitemap.xml');
        file_put_contents($path, $xml);

        $this->info('Sitemap generated: '.$path);
        $this->info(count($urls).' URLs included.');

        return self::SUCCESS;
    }
}
