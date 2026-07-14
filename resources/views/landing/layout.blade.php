<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Primary SEO --}}
    <title>@yield('title', __('landing.site.title')) — {{ __('landing.site.tagline') }}</title>
    <meta name="description" content="@yield('description', __('landing.hero.subheadline'))">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('title', __('landing.site.title')) — {{ __('landing.site.tagline') }}">
    <meta property="og:description" content="@yield('description', __('landing.hero.subheadline'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('/og-image.png'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="og:site_name" content="{{ __('landing.site.title') }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', __('landing.site.title')) — {{ __('landing.site.tagline') }}">
    <meta name="twitter:description" content="@yield('description', __('landing.hero.subheadline'))">
    <meta name="twitter:image" content="@yield('og_image', asset('/og-image.png'))">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js'])
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- JSON-LD Structured Data --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@graph": [
            {
                "@@type": "Organization",
                "@@id": "{{ url('/') }}/#organization",
                "name": "{{ __('landing.site.title') }}",
                "url": "{{ url('/') }}",
                "logo": {
                    "@@type": "ImageObject",
                    "url": "{{ asset('/logo.svg') }}",
                    "width": "512",
                    "height": "512"
                },
                "description": "{{ __('landing.hero.subheadline') }}",
                "contactPoint": {
                    "@@type": "ContactPoint",
                    "contactType": "sales",
                    "availableLanguage": ["Portuguese", "English"]
                }
            },
            {
                "@@type": "WebSite",
                "@@id": "{{ url('/') }}/#website",
                "url": "{{ url('/') }}",
                "name": "{{ __('landing.site.title') }}",
                "description": "{{ __('landing.hero.subheadline') }}",
                "publisher": { "@@id": "{{ url('/') }}/#organization" },
                "inLanguage": "{{ str_replace('_', '-', app()->getLocale()) }}"
            }
            @stack('jsonld_graph')
        ]
    }
    </script>
    @stack('jsonld')
</head>
<body class="bg-white text-gray-900 antialiased font-sans">

    {{-- Navigation --}}
    <header class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur border-b border-gray-100">
        <nav class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
            <a href="/" class="text-lg font-bold tracking-tight text-gray-900 flex items-center gap-2">
                <img src="/logo.svg" alt="{{ __('landing.site.title') }} logo" class="w-8 h-8 rounded-lg">
                {{ __('landing.site.title') }}
            </a>

            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="{{ how_it_works_url() }}" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.how_it_works') }}</a>
                <a href="{{ industries_url() }}" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.industries') }}</a>
                <a href="#pricing" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.pricing') }}</a>
                <a href="#demo" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.demo') }}</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="/admin" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                    {{ __('landing.nav.login') }}
                </a>
                <a href="#demo" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-amber-500 rounded-lg hover:bg-amber-600 transition-colors shadow-sm">
                    {{ __('landing.hero.cta_primary') }}
                </a>
            </div>
        </nav>
    </header>

    {{-- Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 pt-32 pb-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 pt-4">
                {{-- Brand --}}
                <div class="lg:col-span-1">
                    <a href="/" class="flex items-center gap-2 text-white font-bold text-lg mb-4">
                        <img src="/logo.svg" alt="{{ __('landing.site.title') }} logo" class="w-8 h-8 rounded-lg">
                        {{ __('landing.site.title') }}
                    </a>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ __('landing.hero.subheadline') }}</p>
                </div>

                {{-- Soluções --}}
                <div>
                    <p class="text-white font-semibold text-sm mb-4">{{ __('landing.nav.industries') }}</p>
                    <div class="space-y-2.5">
                        @php
                            $industries = __('landing.industries_section');
                            $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);
                        @endphp
                        @foreach ($trades as $key => $trade)
                            <a href="{{ industry_url($key) }}" class="block text-sm text-gray-500 hover:text-white transition-colors">
                                {{ $trade['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Plataforma --}}
                <div>
                    <p class="text-white font-semibold text-sm mb-4">Plataforma</p>
                    <div class="space-y-2.5">
                        <a href="{{ how_it_works_url() }}" class="block text-sm text-gray-500 hover:text-white transition-colors">
                            {{ __('landing.nav.how_it_works') }}
                        </a>
                        <a href="#demo" class="block text-sm text-gray-500 hover:text-white transition-colors">
                            {{ __('landing.hero.cta_primary') }}
                        </a>
                    </div>
                </div>

                {{-- Empresa --}}
                <div>
                    <p class="text-white font-semibold text-sm mb-4">Empresa</p>
                    <div class="space-y-2.5">
                        <a href="#" class="block text-sm text-gray-500 hover:text-white transition-colors">{{ __('landing.footer.privacy') }}</a>
                        <a href="#" class="block text-sm text-gray-500 hover:text-white transition-colors">{{ __('landing.footer.terms') }}</a>
                        <a href="#" class="block text-sm text-gray-500 hover:text-white transition-colors">{{ __('landing.footer.contact') }}</a>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-800 text-center text-sm text-gray-600">
                &copy; {{ date('Y') }} {{ __('landing.footer.copyright') }}
            </div>
        </div>
    </footer>
</body>
</html>
