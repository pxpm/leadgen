<section id="demo" class="py-28 px-6 bg-white">
    <div class="max-w-2xl mx-auto">
        <div class="text-center">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.demo_form.headline') }}
            </h2>
            <p class="mt-4 text-gray-500">{{ __('landing.demo_form.subtitle') }}</p>
        </div>

        @include('landing.partials._demo_form')
    </div>
</section>
