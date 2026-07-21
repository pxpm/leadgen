<section class="relative py-32 px-6 bg-gray-900 overflow-hidden">
    <div class="absolute inset-0 bg-dots-dark opacity-20"></div>
    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-amber-500/5 rounded-full blur-3xl"></div>

    <div class="relative max-w-2xl mx-auto text-center">
        <h2 class="text-3xl sm:text-5xl font-bold tracking-tight text-white leading-tight">
            {{ __('landing.final_cta.headline') }}
        </h2>
        <p class="mt-6 text-gray-400 text-lg">{{ __('landing.final_cta.subtext') }}</p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <button @click="showTrialModal = true" class="group inline-flex items-center px-8 py-4 text-lg font-bold text-gray-900 bg-amber-500 rounded-2xl hover:bg-amber-400 transition-all shadow-2xl shadow-amber-500/30 cursor-pointer">
                {{ __('landing.final_cta.cta') }}
                <span class="ml-2 transition-transform group-hover:translate-x-1">→</span>
            </button>
            <span class="text-gray-500 text-sm">Sem compromisso. Demo gratuita.</span>
        </div>
    </div>
</section>
