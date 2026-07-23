@php
    use App\Models\Tenant;
    $impersonatingTenantId = request()->cookie('impersonating_tenant_id');
    $impersonatingTenant = $impersonatingTenantId ? Tenant::find($impersonatingTenantId) : null;
@endphp

@if($impersonatingTenant && auth()->user()?->isSuperAdmin())
    <div class="fi-impersonation-banner sticky top-0 z-50 flex items-center justify-between gap-4 bg-warning-500 px-4 py-2 text-sm font-medium text-white shadow-md">
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-o-eye" class="h-5 w-5" />
            <span>
                A visualizar como <strong>{{ $impersonatingTenant->name }}</strong>
            </span>
        </div>
        <form action="{{ route('impersonation.stop') }}" method="POST">
            @csrf
            <x-filament::button
                tag="button"
                type="submit"
                color="danger"
                size="sm"
            >
                Terminar Impersonação
            </x-filament::button>
        </form>
    </div>
@endif
