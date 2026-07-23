<section class="relative py-24 sm:py-32 px-6 bg-white overflow-hidden">
    {{-- Subtle bg texture --}}
    <div class="absolute inset-0 bg-dots opacity-30"></div>

    <div class="relative max-w-7xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                <span class="text-gray-900">{{ __('landing.problem.headline') }}</span>
                <span class="text-amber-500">{{ __('landing.problem.headline_2') }}</span>
            </h2>
        </div>

        {{-- Two columns — asymmetric, floating --}}
        <div class="mt-16 grid lg:grid-cols-2 gap-12 lg:gap-20">

            {{-- ═══ LEFT: Lose Leads ═══ --}}
            <div class="space-y-8">
                <div class="line-decoration pl-2">
                    <p class="text-xs uppercase tracking-widest font-bold mb-2">
                        <span class="text-gray-500">O problema da</span>
                        <span class="text-amber-500">resposta</span>
                    </p>
                    <h3 class="text-2xl sm:text-3xl font-serif-display text-gray-900">{{ __('landing.problem.left_title') }}</h3>
                </div>

                {{-- Pain points — editorial, sharp --}}
                <div class="space-y-5">
                    @foreach (['stat_1', 'stat_2', 'stat_3'] as $idx => $stat)
                        <div class="flex items-start gap-4 {{ $idx === 1 ? 'ml-8' : ($idx === 2 ? '-ml-2' : '') }}">
                            <span class="shrink-0 mt-1 w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <p class="text-base text-gray-700 leading-relaxed pt-1">{{ __('landing.problem.left_stats.'.$stat) }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Consequence --}}
                <div class="relative ml-6 border-l-2 border-red-200 pl-5 py-2">
                    <p class="text-sm font-medium text-red-700 leading-relaxed">
                        {{ __('landing.problem.left_result') }}
                    </p>
                </div>
            </div>

            {{-- ═══ RIGHT: Lose Time ═══ --}}
            <div class="space-y-8">
                <div class="line-decoration pl-2">
                    <p class="text-xs uppercase tracking-widest font-bold mb-2">
                        <span class="text-gray-500">O problema da</span>
                        <span class="text-amber-500">qualificação</span>
                    </p>
                    <h3 class="text-2xl sm:text-3xl font-serif-display text-gray-900">{{ __('landing.problem.right_title') }}</h3>
                </div>

                {{-- Pain points — editorial, sharp --}}
                <div class="space-y-5">
                    @foreach (['stat_1', 'stat_2', 'stat_3'] as $idx => $stat)
                        <div class="flex items-start gap-4 {{ $idx === 1 ? 'ml-8' : ($idx === 2 ? '-ml-2' : '') }}">
                            <span class="shrink-0 mt-1 w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <p class="text-base text-gray-700 leading-relaxed pt-1">{{ __('landing.problem.right_stats.'.$stat) }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Consequence --}}
                <div class="relative ml-6 border-l-2 border-amber-200 pl-5 py-2">
                    <p class="text-sm font-medium text-amber-700 leading-relaxed">
                        {{ __('landing.problem.right_result') }}
                    </p>
                </div>
            </div>

        </div>
    </div>

    @include('landing.partials._connector')
</section>
