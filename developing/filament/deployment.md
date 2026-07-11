# Deployment (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/deployment
### Build Assets
`npm run build` or `php artisan filament:assets`
### Cache
`php artisan optimize` `php artisan icons:cache` `php artisan filament:cache-components`
### Production
Set `APP_ENV=production` `APP_DEBUG=false`. Implement `FilamentUser::canAccessPanel()`.
### Scheduler
For database notifications/queues: `php artisan schedule:work`
