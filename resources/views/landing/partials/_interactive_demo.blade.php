<section id="demo" class="py-28 px-6 bg-white">
    <div class="max-w-2xl mx-auto">
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 text-xs font-bold tracking-wider uppercase rounded-full mb-6">Demo</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.demo_form.headline') }}
            </h2>
            <p class="mt-4 text-gray-500">{{ __('landing.demo_form.subtitle') }}</p>
        </div>

        @include('landing.partials._demo_form')
    </div>
</section>
