<section class="relative pt-36 pb-24 px-6 overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-30"></div>

    <div class="relative max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <div class="max-w-xl">
                <div class="line-decoration mb-10">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-gray-900 leading-[1.08]">
                        {{ __('landing.hero.headline') }}
                    </h1>
                </div>
                <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-lg">
                    {{ __('landing.hero.subheadline') }}
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <button @click="showTrialModal = true" class="group inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 cursor-pointer">
                        {{ __('landing.hero.cta_primary') }}
                        <span class="ml-2 transition-transform group-hover:translate-x-1">→</span>
                    </button>
                    <a href="#how-it-works" class="inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                        {{ __('landing.hero.cta_secondary') }}
                    </a>
                </div>
            </div>

            {{-- Conversation card --}}
            <div class="relative">
                <div class="absolute -inset-1 bg-gradient-to-br from-amber-200/40 to-gray-200/40 rounded-[2rem] blur-xl"></div>
                <div class="relative bg-white border border-gray-200/80 rounded-2xl p-5 shadow-2xl shadow-gray-200/60">
                    <div class="flex items-center gap-2 mb-4 pb-4 border-b border-gray-100">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                        <span class="ml-2 text-xs text-gray-400 font-mono tracking-wide">assistente-de-orcamentos.pt</span>
                    </div>
                    <div class="space-y-4">
                        <div class="flex gap-3 items-start">
                            <span class="shrink-0 w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-500 mt-0.5">C</span>
                            <div class="bg-gray-50 border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm text-gray-700 max-w-[78%] shadow-sm leading-relaxed">
                                {{ __('landing.hero.conversation.customer_1') }}
                            </div>
                        </div>
                        <div class="flex gap-3 items-start justify-end">
                            <div class="bg-amber-50 border border-amber-100 rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm text-gray-700 max-w-[78%] shadow-sm leading-relaxed">
                                {{ __('landing.hero.conversation.assistant_1') }}
                            </div>
                            <span class="shrink-0 w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center text-xs font-bold text-amber-600 mt-0.5">A</span>
                        </div>
                        <div class="flex gap-3 items-start">
                            <span class="shrink-0 w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-500 mt-0.5">C</span>
                            <div class="bg-gray-50 border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm text-gray-700 max-w-[78%] shadow-sm leading-relaxed">
                                {{ __('landing.hero.conversation.customer_2') }}
                            </div>
                        </div>
                        <div class="flex gap-3 items-start justify-end">
                            <div class="bg-amber-50 border border-amber-100 rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm text-gray-700 max-w-[78%] shadow-sm leading-relaxed">
                                {{ __('landing.hero.conversation.assistant_2') }}
                            </div>
                            <span class="shrink-0 w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center text-xs font-bold text-amber-600 mt-0.5">A</span>
                        </div>
                    </div>
                    <div class="mt-5 flex items-center gap-2 px-4 py-2.5 bg-green-50/80 border border-green-200/80 rounded-xl text-sm font-semibold text-green-700">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('landing.hero.conversation.result') }}
                    </div>
                </div>
                <div class="absolute -z-10 -top-8 -right-8 w-40 h-40 bg-amber-100/60 rounded-[2.5rem] rotate-12"></div>
                <div class="absolute -z-10 -bottom-6 -left-6 w-28 h-28 bg-gray-100/60 rounded-[2rem] -rotate-6"></div>
            </div>
        </div>
    </div>
</section>
