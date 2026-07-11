<section id="demo" class="py-28 px-6 bg-white">
    <div class="max-w-3xl mx-auto">
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 text-xs font-bold tracking-wider uppercase rounded-full mb-6">Demo Interativa</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.interactive_demo.headline') }}
            </h2>
            <p class="mt-4 text-gray-500">{{ __('landing.interactive_demo.subtitle') }}</p>
        </div>

        <div class="mt-12 bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-xl shadow-gray-200/50">
            <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                <span class="text-xs text-gray-400 font-mono tracking-wide">demo.leadassistant.pt</span>
            </div>
            <div class="p-6 h-72 overflow-y-auto space-y-4 text-left bg-white">
                <div class="flex gap-3 items-start">
                    <span class="shrink-0 w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-sm font-bold text-amber-600 mt-0.5">A</span>
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-gray-700 max-w-[80%] shadow-sm leading-relaxed">
                        Olá! Em que podemos ajudar no seu projeto hoje?
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 p-4 flex gap-3 bg-gray-50/50">
                <input type="text" placeholder="{{ __('landing.interactive_demo.placeholder') }}" class="flex-1 px-4 py-3 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
                <button class="shrink-0 px-5 py-3 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-colors font-semibold text-sm">→</button>
            </div>
        </div>

        <p class="mt-6 text-sm text-gray-400 text-center">{{ __('landing.interactive_demo.try_examples') }}</p>
        <div class="mt-3 flex flex-wrap justify-center gap-2">
            <button class="px-4 py-2 text-sm font-medium bg-gray-100 rounded-full hover:bg-amber-50 hover:text-amber-700 transition-colors">O meu telhado está com infiltrações</button>
            <button class="px-4 py-2 text-sm font-medium bg-gray-100 rounded-full hover:bg-amber-50 hover:text-amber-700 transition-colors">Preciso de um orçamento para pintura exterior</button>
            <button class="px-4 py-2 text-sm font-medium bg-gray-100 rounded-full hover:bg-amber-50 hover:text-amber-700 transition-colors">Quero substituir as janelas da casa</button>
        </div>
    </div>
</section>
