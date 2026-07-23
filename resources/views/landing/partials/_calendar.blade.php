<section class="relative py-28 px-6 bg-white overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-30"></div>

    <div class="relative max-w-6xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.calendar.headline') }}
            </h2>
            <p class="mt-4 text-gray-500 text-lg">{{ __('landing.calendar.subtitle') }}</p>
        </div>

        <div class="mt-14 grid md:grid-cols-3 gap-6">
            @php
                $features = [
                    ['icon' => 'followup', 'color' => 'blue', 'key' => 'followups'],
                    ['icon' => 'visit', 'color' => 'green', 'key' => 'visits'],
                    ['icon' => 'task', 'color' => 'amber', 'key' => 'tasks'],
                ];
            @endphp

            @foreach ($features as $f)
                <div class="bg-white border border-gray-200 rounded-2xl p-8 hover:shadow-lg hover:border-gray-300 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-{{ $f['color'] }}-50 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        @if ($f['icon'] === 'followup')
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/><circle cx="12" cy="12" r="10"/></svg>
                        @elseif ($f['icon'] === 'visit')
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        @else
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('landing.calendar.'.$f['key'].'.title') }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ __('landing.calendar.'.$f['key'].'.desc') }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <p class="font-semibold text-amber-800">{{ __('landing.calendar.reminders.title') }}</p>
            <p class="text-sm text-amber-600">{{ __('landing.calendar.reminders.desc') }}</p>
        </div>
    </div>
</section>
