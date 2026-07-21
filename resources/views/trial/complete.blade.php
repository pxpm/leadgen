@extends('landing.layout')

@section('title', 'Completar Trial')

@section('content')
<section class="relative pt-36 pb-20 px-6 bg-white">
    <div class="max-w-lg mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Completar Registo</h1>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <span class="text-blue-500 text-lg">{{ $socialProvider === 'google' ? 'G' : 'f' }}</span>
            <div>
                <p class="text-sm font-semibold text-blue-800">Autenticado como {{ $socialEmail }}</p>
                <p class="text-xs text-blue-600">{{ $socialName }}</p>
            </div>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-600">
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('trial.complete') }}" class="space-y-5">
            @csrf

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nome</label>
                    <input type="text" value="{{ $socialName }}" disabled
                           class="w-full px-4 py-3 text-sm bg-gray-100 border border-gray-200 rounded-xl text-gray-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                    <input type="email" value="{{ $socialEmail }}" disabled
                           class="w-full px-4 py-3 text-sm bg-gray-100 border border-gray-200 rounded-xl text-gray-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Empresa *</label>
                <input type="text" name="company" value="{{ old('company') }}" required
                       class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
                @error('company') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Telefone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Especialidade *</label>
                <select name="industry" id="industry" required
                        class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
                    <option value="">Selecione a sua área</option>
                    @foreach ($industries as $ind)
                        <option value="{{ $ind->slug }}" {{ old('industry') == $ind->slug ? 'selected' : '' }}>
                            {{ $ind->name }}
                        </option>
                    @endforeach
                    <option value="outro" {{ old('industry') == 'outro' ? 'selected' : '' }}>Outro</option>
                </select>
                @error('industry') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div id="industry-other-wrapper" style="display: {{ old('industry') === 'outro' ? 'block' : 'none' }}">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Qual a sua especialidade? *</label>
                <input type="text" name="industry_other" value="{{ old('industry_other') }}"
                       class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
                @error('industry_other') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                    class="w-full px-6 py-3.5 text-base font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20">
                Começar Trial Gratuito
            </button>
        </form>
    </div>
</section>

<script>
document.getElementById('industry').addEventListener('change', function() {
    document.getElementById('industry-other-wrapper').style.display =
        this.value === 'outro' ? 'block' : 'none';
});
</script>
@endsection
