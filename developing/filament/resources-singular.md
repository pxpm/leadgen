# Singular Resources (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/singular
For one-record resources (settings, homepage): `protected static bool $isSingular = true`
No List page. Only Edit at `/admin/settings` (no suffix).
`SettingsResource::getUrl()` returns `/admin/settings` directly.
