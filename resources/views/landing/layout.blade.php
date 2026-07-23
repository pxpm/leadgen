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
        @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js', 'resources/js/landing.js'])
    @endif

    {{-- Alpine x-cloak fallback — hides Alpine-controlled elements before JS initializes --}}
    <style>[x-cloak] { display: none !important; }</style>

    {{-- Turnstile — only when configured --}}
    @if(config('services.turnstile.site_key'))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif

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
<body class="bg-white text-gray-900 antialiased font-sans" x-data="{ showTrialModal: false }">

    {{-- Navigation --}}
    <header class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur border-b border-gray-100 overflow-hidden">
        <nav class="max-w-7xl mx-auto flex items-center justify-between px-3 sm:px-6 py-3 sm:py-4 min-w-0">
            <a href="/" class="text-base sm:text-lg font-bold tracking-tight text-gray-900 flex items-center gap-2 min-w-0 shrink">
                <img src="/logo.svg" alt="{{ __('landing.site.title') }} logo" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg shrink-0">
                <span class="truncate">{{ __('landing.site.title') }}</span>
            </a>

            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="{{ how_it_works_url() }}" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.how_it_works') }}</a>
                <a href="{{ industries_url() }}" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.industries') }}</a>
                <a href="{{ pricing_url() }}" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.pricing') }}</a>
                <a href="/blog" class="hover:text-gray-900 transition-colors">Guia</a>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <a href="/manage-backoffice/login" class="text-xs sm:text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors shrink-0">
                    {{ __('landing.nav.login') }}
                </a>
                <button @click="showTrialModal = true" class="inline-flex items-center px-2.5 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-white bg-amber-500 rounded-lg hover:bg-amber-600 transition-colors shadow-sm cursor-pointer shrink-0 whitespace-nowrap">
                    {{ __('landing.hero.cta_primary') }}
                </button>
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

                {{-- Plataforma + Regiões --}}
                <div>
                    <p class="text-white font-semibold text-sm mb-4">Plataforma</p>
                    <div class="space-y-2.5 mb-6">
                        <a href="{{ how_it_works_url() }}" class="block text-sm text-gray-500 hover:text-white transition-colors">{{ __('landing.nav.how_it_works') }}</a>
                        <a href="{{ pricing_url() }}" class="block text-sm text-gray-500 hover:text-white transition-colors">{{ __('landing.nav.pricing') }}</a>
                        <a href="/blog" class="block text-sm text-gray-500 hover:text-white transition-colors">Guia para Empresas</a>
                    </div>
                    <p class="text-white font-semibold text-sm mb-4">Regiões</p>
                    <div class="space-y-2.5">
                        <a href="/orcamentos-lisboa" class="block text-sm text-gray-500 hover:text-white transition-colors">Lisboa</a>
                        <a href="/orcamentos-porto" class="block text-sm text-gray-500 hover:text-white transition-colors">Porto</a>
                        <a href="/orcamentos-algarve" class="block text-sm text-gray-500 hover:text-white transition-colors">Algarve</a>
                        <a href="/orcamentos-minho" class="block text-sm text-gray-500 hover:text-white transition-colors">Minho</a>
                        <a href="/orcamentos-alentejo" class="block text-sm text-gray-500 hover:text-white transition-colors">Alentejo</a>
                    </div>
                </div>

                {{-- Empresa --}}
                <div>
                    <p class="text-white font-semibold text-sm mb-4">Empresa</p>
                    <div class="space-y-2.5">
                        <a href="{{ privacy_url() }}" class="block text-sm text-gray-500 hover:text-white transition-colors">{{ __('landing.footer.privacy') }}</a>
                        <a href="{{ terms_url() }}" class="block text-sm text-gray-500 hover:text-white transition-colors">{{ __('landing.footer.terms') }}</a>
                        <a href="{{ contact_url() }}" class="block text-sm text-gray-500 hover:text-white transition-colors">{{ __('landing.footer.contact') }}</a>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-800 text-center text-sm text-gray-600">
                &copy; {{ date('Y') }} {{ __('landing.footer.copyright') }}
            </div>
        </div>
    </footer>

    {{-- Trial Signup Modal --}}
    <div x-show="showTrialModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-transition.opacity>
        {{-- Backdrop --}}
        <div x-show="showTrialModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showTrialModal = false" x-transition.opacity></div>

        {{-- Modal --}}
        <div x-show="showTrialModal" x-cloak class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden" @click.outside="showTrialModal = false" x-transition.scale>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">{{ __('landing.demo_form.headline') }}</h3>
                <button @click="showTrialModal = false" class="p-1.5 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 py-5">
                @include('landing.partials._demo_form')
            </div>
        </div>
    </div>

</body>
</html>
