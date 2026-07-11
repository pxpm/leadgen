# Panel Configuration (Filament v5)

**URL:** https://filamentphp.com/docs/5.x/panel-configuration

## Key Takeaways

Configuration file: `app/Providers/Filament/AdminPanelProvider.php`.

## Creating Panels

```bash
php artisan make:filament-panel app   # Creates AppPanelProvider.php
```

Each panel has its own resources, pages, widgets.

## Common Methods

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->path('admin')                    // URL path
        ->domain('admin.example.com')      // Domain restriction
        ->maxContentWidth(Width::Full)     // Content width
        ->simplePageMaxContentWidth(Width::Small)
        ->subNavigationPosition(SubNavigationPosition::End)
        ->spa()                            // SPA mode with wire:navigate
        ->spa(hasPrefetching: true)        // Prefetch on hover
        ->spaUrlExceptions([url('/admin')])
        ->unsavedChangesAlerts()
        ->databaseTransactions()
        ->broadcasting(false)
        ->strictAuthorization()
        ->sidebarCollapsibleOnDesktop()
        ->sidebarFullyCollapsibleOnDesktop()
        ->topNavigation()
        ->breadcrumbs(false)
        ->collapsibleNavigationGroups(false);
}
```

## Lifecycle Hooks

```php
->bootUsing(function (Panel $panel) { /* runs on every request */ })
```

## Assets

```php
->assets([
    Css::make('custom', resource_path('css/custom.css')),
    Js::make('custom', resource_path('js/custom.js')),
])
// Then run: php artisan filament:assets
```

## Middleware

```php
->middleware([...])           // All routes
->authMiddleware([...])       // Authenticated routes only
// isPersistent: true to run on every Livewire AJAX request
```

## Render Hooks

```php
->renderHook(
    PanelsRenderHook::BODY_START,
    fn (): string => Blade::render("@livewire('my-component')"),
)
```

## Error Notifications

```php
->registerErrorNotification(title: 'Error', body: 'Try again.')
->registerErrorNotification(title: 'Not Found', body: '...', statusCode: 404)
->hiddenErrorNotification(403)
->disabledErrorNotification(503)
```

## Navigation

```php
->navigation(false)                              // Disable entirely
->navigation(function (NavigationBuilder $builder) {
    return $builder->items([...])->groups([...]);
})
->navigationGroups([NavigationGroup::make()->label('Shop')->icon(...)])
```
