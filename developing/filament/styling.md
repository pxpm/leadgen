# Styling (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/styling/overview
Filament uses Tailwind CSS with semantic classes (`.fi-btn`). Customize via CSS overrides.
### Theme
`php artisan filament:theme` creates `resources/css/filament/admin/theme.css`
### Custom Colors
In panel: `->colors(['primary'=>Color::Amber,'danger'=>Color::Rose])`
### Brand
`->brandName('My App')` `->brandLogo(asset('img/logo.svg'))` `->favicon(asset('favicon.ico'))`
### Dark Mode
`->darkMode(true)` default. `->darkMode(false)` disable.
