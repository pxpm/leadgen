# Users (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/users/overview
### FilamentUser Contract
```php
use Filament\Models\Contracts\FilamentUser;
class User extends Authenticatable implements FilamentUser {
    public function canAccessPanel(Panel $panel): bool { return true; }
}
```
### Multi-Tenancy: `->tenant(Organization::class)` on panel.
### Multi-Factor Auth: Set up MFA via `->multiFactorAuthentication()` on panel.
### Custom User Model: Filament uses App\Models\User by default. Change via config.
