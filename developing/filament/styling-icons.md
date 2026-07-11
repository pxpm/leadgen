# Styling Icons (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/styling/icons
### Heroicons
Filament ships with Blade Heroicons. Use: `Heroicon::OutlinedUser` or string `'heroicon-o-user'`
### Custom Icons
Register via `Filamenticon::register(['my-icon'=>resource_path('svg/my-icon.svg')])`
### Icon Sizes
`IconSize::Small` `IconSize::Medium` `IconSize::Large`
### Using in Actions/Columns
`->icon('heroicon-o-user')` `->icon(Heroicon::OutlinedUser)`
