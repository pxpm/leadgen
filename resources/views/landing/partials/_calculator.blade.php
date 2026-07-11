<section class="relative py-28 px-6 bg-gray-900 overflow-hidden">
    <div class="absolute inset-0 bg-dots-dark opacity-20"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl"></div>

    <div class="relative max-w-4xl mx-auto" x-data="{
        avgValue: 5000,
        monthlyLeads: 20,
        missedLeads: 5,
        get lostRevenue() { return this.avgValue * this.missedLeads; },
        format(n) { return new Intl.NumberFormat('pt-PT').format(n); }
    }">
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/10 text-amber-400 text-xs font-bold tracking-wider uppercase rounded-full mb-6">Calculadora de Receita</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-white leading-tight">
                {{ __('landing.calculator.title') }}
            </h2>
            <p class="mt-4 text-gray-400 max-w-md mx-auto">{{ __('landing.calculator.subtitle') }}</p>
        </div>

        <div class="mt-14 grid md:grid-cols-4 gap-5">
            <div class="md:col-span-3 grid sm:grid-cols-3 gap-4">
                @php
                    $sliders = [
                        ['label' => 'calculator.avg_project_value', 'model' => 'avgValue', 'min' => 500, 'max' => 50000, 'step' => 500, 'suffix' => ' €'],
                        ['label' => 'calculator.monthly_leads', 'model' => 'monthlyLeads', 'min' => 5, 'max' => 100, 'step' => 1, 'suffix' => ''],
                        ['label' => 'calculator.missed_leads', 'model' => 'missedLeads', 'min' => 1, 'max' => 30, 'step' => 1, 'suffix' => ''],
                    ];
                @endphp
                @foreach ($sliders as $s)
                <div class="bg-gray-800/80 border border-gray-700/50 rounded-2xl p-5 backdrop-blur">
                    <label class="text-xs text-gray-400 uppercase tracking-wider font-semibold">{{ __("landing.{$s['label']}") }}</label>
                    <div class="mt-2 text-2xl font-bold text-white" x-text="{{ $s['suffix'] ? "format({$s['model']}).replace(',',' ') + '{$s['suffix']}'" : $s['model'] }}"></div>
                    <input type="range" x-model="{{ $s['model'] }}" min="{{ $s['min'] }}" max="{{ $s['max'] }}" step="{{ $s['step'] }}" class="mt-3 w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-amber-500 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-amber-500 [&::-webkit-slider-thumb]:shadow-lg">
                </div>
                @endforeach
            </div>

            <div class="relative bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 flex flex-col justify-center overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <p class="text-xs text-amber-100 uppercase tracking-wider font-semibold relative">{{ __('landing.calculator.result') }}</p>
                <p class="mt-3 text-4xl font-black text-white relative" x-text="format(lostRevenue) + ' €'"></p>
                <p class="text-sm text-amber-100 relative">{{ __('landing.calculator.per_month') }}</p>
            </div>
        </div>

        <p class="mt-8 text-center text-gray-500 text-sm">{{ __('landing.calculator.cta') }}</p>
    </div>
</section>
