<section id="how-it-works" class="relative pt-28 pb-28 px-6 bg-gray-50/70 overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-40"></div>

    <div class="relative max-w-7xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.solution.headline') }}
            </h2>
        </div>

        <div class="mt-16 grid md:grid-cols-3 gap-6 items-start">

            {{-- Card 1: Qualification — left, regular --}}
            <div class="card-lift relative bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="h-1.5 bg-amber-300"></div>
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="shrink-0 w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                        <h3 class="text-lg font-serif-display text-gray-900">{{ __('landing.solution.qualify.title') }}</h3>
                    </div>
                    <p class="text-gray-500 leading-relaxed text-sm">{{ __('landing.solution.qualify.description') }}</p>
                    <div class="mt-5 flex flex-wrap gap-1.5">
                        @foreach (__('landing.solution.qualify.tags') as $tag)
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-medium rounded-md">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Card 2: Follow-up with AI — center, elevated, hero --}}
            <div class="card-lift relative bg-white border-2 border-amber-300 rounded-2xl overflow-hidden -mt-4 md:-mt-8 shadow-xl shadow-amber-100/50">
                <div class="h-1.5 bg-amber-500"></div>
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="shrink-0 w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-serif-display text-gray-900">{{ __('landing.solution.followup.title') }}</h3>
                    </div>
                    <p class="text-gray-500 leading-relaxed text-sm">{{ __('landing.solution.followup.description') }}</p>
                    <div class="mt-5 flex flex-wrap gap-1.5">
                        @foreach (__('landing.solution.followup.tags') as $tag)
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-md">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Card 3: All Channels — right, regular --}}
            <div class="card-lift relative bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="h-1.5 bg-amber-400"></div>
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="shrink-0 w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <h3 class="text-lg font-serif-display text-gray-900">{{ __('landing.solution.channels.title') }}</h3>
                    </div>
                    <p class="text-gray-500 leading-relaxed text-sm">{{ __('landing.solution.channels.description') }}</p>
                    <div class="mt-5 flex flex-wrap gap-1.5">
                        @foreach (__('landing.solution.channels.tags') as $tag)
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-medium rounded-md">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
