# Filament v5 Gotchas

## 1. `modifyQueryUsing` is NOT a chainable method in v5
In Filament v3: `->relationship('teacher', 'name')->modifyQueryUsing(fn ($q) => ...)`
In Filament v5: `->relationship('teacher', 'name', fn ($q) => ...)` — it's the **3rd parameter** of `relationship()`.

## 2. Magic `for[Relation]()` methods pass Model to `state()`, breaking `array_merge`
`->forSchool($model)` in Laravel 13 resolves to `for(School::factory()->state($model), 'school')`. Since `state($model)` wraps non-callable in `fn () => $model`, the closure returns a Model instead of an array. Use `->for($model)` instead.

## 3. Laravel 13 `for()` stores BelongsToRelationship objects, not states
The `for()` method now stores relationships in `$this->for` as BelongsToRelationship objects. The parent resolutions happen via `parentResolvers()` which is prepended as the first state closure.

## 4. Filament v5 relationship selects use global scopes automatically
When a model has a global scope (like BySchoolScope), relationship selects automatically apply it. No need for modifyQueryUsing when the related model already has the scope.

## 5. EVERY form must have a test that renders create/edit pages
Tests that simply visit `->get(Resource::getUrl('create'))` as different roles catch BadMethodCallException from wrong method names immediately.

## 6. SQLite can't ALTER COLUMN SET DEFAULT
Don't create migrations that change column defaults — they work on MySQL/Postgres but fail silently on SQLite. Use model-level defaults instead.

## 7. `getSettingsAttribute` accessor fires BEFORE `casts`
When using both `casts()` array cast AND a custom accessor for settings, the accessor receives the raw DB value (string/null) before the cast fires. Handle both types in the accessor.

## 8. School scoping pattern
- Models with `school_id`: use `BySchoolScope` global scope
- Models without `school_id`: add `booted()` with `whereHas` through parent chain
- Form school_id selects: `->visible(fn () => auth()->user()?->hasRole('super-admin'))->default(fn () => auth()->user()?->school_id)`

## 9. Custom page properties must match parent types exactly (PHP 8.5)
- `$navigationGroup`: `string|UnitEnum|null` (parent uses `UnitEnum`, not `BackedEnum`)
- `$navigationIcon`: `string|BackedEnum|null` (parent uses `BackedEnum`)
- `$view`: non-static (`protected string $view`)
- Using `?string` or `string` will cause a `FatalError` because PHP enforces invariant property types.

## 10. Multi-Tenancy: Filament tenancy + Spatie HasRoles on the same User model
`User` must implement BOTH `HasTenants` (Filament) and `HasRoles` (Spatie). Watch for:
- `HasRoles` adds a `roles()` BelongsToMany — do NOT name the tenant relationship `roles`
- `HasTenants` requires `getTenants()` and `canAccessTenant()` — these must return the Organization BelongsToMany
- If using Spatie's `team_id` feature alongside Filament tenancy, the two tenant systems will conflict. We intentionally do NOT use Spatie's team feature — roles are global, tenant context is Filament-only.
- **Arch test enforces:** User implements HasTenants, User does NOT use Spatie team features.

## 11. Multi-Tenancy: URL generation in queued jobs has NO request context
When a Job/Mailable/Notification runs in the queue, `request()` is null and `Filament::getTenant()` may return null.
- Always pass the `organization_id` (or Organization model) explicitly to queued jobs.
- In the job's `handle()`, call `filament()->setTenant($organization)` or use `url()->forceRootUrl()` for the org's subdomain.
- **Arch test enforces:** All Mailable classes declare an `$organization` property or accept one in constructor.

## 12. Multi-Tenancy: Signed URLs must use the correct subdomain
The QR check-in flow uses `URL::signedRoute()`. When generating URLs from a queued job or CLI:
- Force the root URL: `URL::forceRootUrl("https://{$org->domain}." . config('app.domain'))`
- Reset after: `URL::forceRootUrl(config('app.global_url'))`
- Better: wrap in a helper `organization_url($org, $route, $params)` that handles this automatically.

## 13. Multi-Tenancy: Global scopes vs Filament tenancy scoping
Before tenancy: we used `ByOrganizationScope` global scope on each model. After switching to Filament tenancy:
- Filament automatically applies its own tenant scope to tenant-aware resources
- Models that had `ByOrganizationScope` no longer need it — the Filament scope replaces it
- However, queries OUTSIDE the Filament panel (e.g., in Jobs, Commands, or the web route) do NOT have the tenant scope. Use `$org->courses()` (via relationship) instead of `Course::query()` when outside the panel.
- **Arch test enforces:** No model uses `ByOrganizationScope` after tenancy migration; all models use Filament's `HasTenantScope`.

## 14. Multi-Tenancy: `organization_id` FK removal from users
When `organization_id` is removed from the `users` table:
- All code referencing `$user->organization_id` or `$user->organization` breaks
- Replace with `$user->organizations` (BelongsToMany) or `Filament::getTenant()`
- For the "current" organization, use `Filament::getTenant()` which returns the Organization model
- For user's all organizations, use `$user->organizations`
- **Arch test enforces:** No references to `organization_id` on User model.
- `$navigationGroup`: `string|UnitEnum|null` (parent uses `UnitEnum`, not `BackedEnum`)
- `$navigationIcon`: `string|BackedEnum|null` (parent uses `BackedEnum`)
- `$view`: non-static (`protected string $view`)
- Using `?string` or `string` will cause a `FatalError` because PHP enforces invariant property types.
