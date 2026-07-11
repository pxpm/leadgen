# CSS Hooks (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/styling/css-hooks
Filament uses semantic CSS classes: `.fi-btn` `.fi-input` `.fi-table` `.fi-section` `.fi-modal`
### Override in theme.css: `.fi-btn { @apply rounded-sm; }` `.fi-input-wrp { @apply shadow-none; }`
### Dark mode: `.dark .fi-btn { ... }`
### Custom theme: `php artisan filament:theme` creates `resources/css/filament/admin/theme.css`
### Build: `npm run build` after changes. Then `->viteTheme("resources/css/filament/admin/theme.css")` in panel.
