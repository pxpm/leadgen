# Security (Filament v5)

**URL:** https://filamentphp.com/docs/5.x/advanced/security

## Key Takeaways

### Authorization
- Model Policies auto-checked for resources (viewAny, create, update, view, delete, etc.).
- Custom actions/pages need explicit authorization via `visible()`, `hidden()`, `authorize()`.
- Inline editable columns (ToggleColumn, TextInputColumn, SelectColumn) do NOT check policies. Use `disabled()`.
- `canAccess()` re-runs on every Livewire request.
- **Warning:** `boot()` and `mount()` hooks run BEFORE authorization. Put sensitive work after `$this->authorizeAccess()`.

### Panel Access
- Implement `FilamentUser` contract, define `canAccessPanel(Panel $panel): bool`.
- MFA not enabled by default; only enforced within Filament auth flow.

### Input Validation
- `Str::sanitizeUrl($url)` - allows only http/https by default, rejects javascript:/data: schemes.
- `extraAttributes()` values NOT escaped (needed for Alpine/Livewire directives). Don't pass user data.
- Always validate user-controlled values before passing to Filament methods.

### URL Sanitization
```php
TextColumn::make('website')
    ->url(fn (string $state): ?string => Str::sanitizeUrl($state))
// Allow extra schemes:
Str::sanitizeUrl($state, allowedSchemes: ['http', 'https', 'mailto', 'tel'])
```

### HTML Sanitization
- `html()` and `markdown()` auto-sanitize via Symfony HtmlSanitizer.
- Customize via `HtmlSanitizerConfig` in service provider.
- In Blade: `{!! str($content)->sanitizeHtml() !!}` or `{!! str($content)->markdown()->sanitizeHtml() !!}`.

### Model Attribute Exposure
- All non-$hidden attributes exposed to JS via Livewire.
- Use `mutateFormDataBeforeFill()` to remove sensitive attributes.
- Or add to model's `$hidden` array.

### File Uploads
- Use `RestrictsFileUploadsToSchemaComponents` trait on public-facing Livewire components.
- Hidden file upload fields are rejected as upload targets.

### Query Scoping
- `modifyQueryUsing()` on tables, `getEloquentQuery()` on resources.
- Multi-tenant: scope all custom queries manually.
