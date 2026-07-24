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
        $industries = __('landing.industries_section');
        $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);

        $urls = [
            ['loc' => url('/')],
            ['loc' => how_it_works_url()],
            ['loc' => industries_url()],
            ['loc' => pricing_url()],
            ['loc' => privacy_url()],
            ['loc' => terms_url()],
            ['loc' => contact_url()],
            ['loc' => url('/blog')],
        ];

        // City pages
        foreach (['lisboa', 'porto', 'algarve', 'minho', 'alentejo'] as $city) {
            $urls[] = ['loc' => url('/orcamentos-'.$city)];
        }

        // Industry + service pages
        foreach ($trades as $key => $trade) {
            $urls[] = ['loc' => industry_url($key)];

            $services = __('landing.industry_pages.'.$key.'.services') ?? [];
            foreach ($services as $svc) {
                $svcSlug = $svc['slug'] ?? '';
                if ($svcSlug) {
                    $urls[] = ['loc' => service_url($key, $svcSlug)];
                }
            }
        }

        // Blog posts
        $articles = __('landing.blog_index.articles') ?? [];
        foreach ($articles as $slug => $article) {
            $urls[] = ['loc' => url('/blog/'.$slug)];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.e($url['loc']).'</loc>'."\n";
            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
            }
            $xml .= '  </url>'."\n";
        }
        $xml .= '</urlset>';

        $path = public_path('sitemap.xml');

        $bytes = file_put_contents($path, $xml);

        if ($bytes === false) {
            $this->error('Failed to write sitemap to: '.$path);

            return self::FAILURE;
        }

        $this->info('Sitemap generated: '.$path.' ('.$bytes.' bytes)');
        $this->info(count($urls).' URLs included.');

        return self::SUCCESS;
    }
}
