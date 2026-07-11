# Multi-Tenancy (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/users/tenancy
### Setup
```php
$panel->tenant(Organization::class)
```
### Tenant Registration
`->tenantRegistration(RegisterOrganization::class)` `->tenantProfile(EditOrganizationProfile::class)`
### Scoping
Resources auto-scoped to tenant. Use `ByOrganizationScope` or override `getEloquentQuery()`.
### Tenant Menu
Shown in topbar/header for switching tenants. `->tenantMenu(false)` to disable.
### Custom Tenant Model
Model must implement `HasName` and link to users via `belongsToMany`.
