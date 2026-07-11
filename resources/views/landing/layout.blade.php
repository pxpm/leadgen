<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <title>@yield('title', __('landing.site.title')) — {{ __('landing.site.tagline') }}</title>
    <meta name="description" content="@yield('description', __('landing.hero.subheadline'))">

    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js'])
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
                <a href="#how-it-works" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.how_it_works') }}</a>
                <a href="#industries" class="hover:text-gray-900 transition-colors">{{ __('landing.nav.industries') }}</a>
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
    <footer class="bg-gray-900 text-gray-400 py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2 text-white font-bold text-lg">
                    <span class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center text-white text-sm font-bold">L</span>
                    {{ __('landing.site.title') }}
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <a href="#" class="hover:text-white transition-colors">{{ __('landing.footer.privacy') }}</a>
                    <a href="#" class="hover:text-white transition-colors">{{ __('landing.footer.terms') }}</a>
                    <a href="#" class="hover:text-white transition-colors">{{ __('landing.footer.contact') }}</a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-sm">
                &copy; {{ date('Y') }} {{ __('landing.footer.copyright') }}
            </div>
        </div>
    </footer>
</body>
</html>
