<section class="py-28 px-6 bg-gray-50/70">
    <div class="max-w-3xl mx-auto">
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-200/50 text-gray-500 text-xs font-bold tracking-wider uppercase rounded-full mb-6">FAQ</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.faq.headline') }}
            </h2>
        </div>

        <div class="mt-14 space-y-3" x-data="{ open: null }">
            @foreach (__('landing.faq.items') as $i => $item)
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full px-6 py-5 flex items-center justify-between text-left gap-4">
                        <span class="font-semibold text-gray-900 text-sm sm:text-base">{{ $item['question'] }}</span>
                        <span class="shrink-0 w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center transition-all duration-200" :class="open === {{ $i }} ? 'rotate-180 bg-amber-100 text-amber-600' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse>
                        <div class="px-6 pb-5 text-gray-500 leading-relaxed text-sm border-t border-gray-50 pt-4">
                            {{ $item['answer'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
