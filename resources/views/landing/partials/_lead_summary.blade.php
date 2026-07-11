<section class="relative py-28 px-6 bg-gray-50/70 overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-40"></div>

    <div class="relative max-w-4xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-600 text-xs font-bold tracking-wider uppercase rounded-full mb-6">O Resultado</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.lead_summary.headline') }}
            </h2>
            <p class="mt-4 text-gray-500">{{ __('landing.lead_summary.subtitle') }}</p>
        </div>

        <div class="mt-14 bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-2xl shadow-gray-200/60">
            <div class="bg-gray-900 text-white px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="font-semibold text-sm tracking-wide">Lead Qualificado #2024-0157</span>
                </div>
                <span class="px-3 py-1 bg-green-500/20 text-green-300 text-xs font-bold rounded-full border border-green-500/30">QUALIFICADO</span>
            </div>

            <div class="p-6 sm:p-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">{{ __('landing.lead_summary.customer') }}</p>
                    <p class="font-bold text-gray-900">João Silva</p>
                    <p class="text-sm text-gray-500">joao@email.com</p>
                    <p class="text-sm text-gray-500">912 345 678</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">{{ __('landing.lead_summary.service') }}</p>
                    <p class="font-bold text-gray-900">Substituição de Telhado</p>
                    <span class="inline-block mt-1 px-2.5 py-0.5 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-100">Telha cerâmica</span>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">{{ __('landing.lead_summary.location') }}</p>
                    <p class="font-bold text-gray-900">Porto</p>
                    <p class="text-sm text-gray-500">Rua das Flores, 123</p>
                    <p class="text-sm text-gray-500">4000-000</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">{{ __('landing.lead_summary.photos') }}</p>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-900 text-lg">5</span>
                        <span class="text-sm text-gray-500">fotos anexadas</span>
                    </div>
                    <div class="flex gap-1 mt-2">
                        @for ($i = 0; $i < 5; $i++)
                            <span class="w-8 h-8 bg-gray-100 border border-gray-200 rounded-lg flex items-center justify-center text-xs text-gray-400">📷</span>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 px-6 sm:px-8 py-5 space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">{{ __('landing.lead_summary.urgency') }}</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-700 text-sm font-bold rounded-full border border-red-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Alta — Contactar Hoje
                    </span>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-2">{{ __('landing.lead_summary.summary') }}</p>
                    <p class="text-gray-700 leading-relaxed text-sm bg-gray-50 rounded-xl p-4 border border-gray-100">
                        Cliente precisa de substituir telhado com infiltrações na zona norte da casa. Tem telha cerâmica atualmente e quer substituir por material similar. Enviou 5 fotos do interior e exterior. Urgência alta — a infiltração está a causar danos no teto do quarto. Recomendado contacto imediato para avaliação no local.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
