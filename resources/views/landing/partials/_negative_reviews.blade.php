<section class="py-24 px-6 bg-white overflow-hidden">
    <div class="max-w-6xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-500 text-xs font-bold tracking-wider uppercase rounded-full mb-6">
                ⚠️ Não deixe isto acontecer
            </span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.negative_reviews.headline') }}
            </h2>
            <p class="mt-4 text-gray-500">{{ __('landing.negative_reviews.subtitle') }}</p>
        </div>

        <div class="mt-14 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach (__('landing.negative_reviews.reviews') as $review)
                <div class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-1 mb-3">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 {{ $i < $review['stars'] ? 'text-red-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed mb-4">"{{ $review['text'] }}"</p>
                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span class="font-medium text-gray-500">{{ $review['name'] }}</span>
                        <span>{{ $review['date'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
