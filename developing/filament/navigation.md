# Navigation (Filament v5)

**URL:** https://filamentphp.com/docs/5.x/navigation/overview

## Key Takeaways

### Item Properties
```php
protected static ?string $navigationLabel = 'Custom Label';
protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::OutlinedDocumentText;
protected static ?int $navigationSort = 3;
protected static string|UnitEnum|null $navigationGroup = 'Settings';
protected static ?string $navigationParentItem = 'Notifications';
protected static bool $shouldRegisterNavigation = false;
```

### Badge
```php
public static function getNavigationBadge(): ?string { return static::getModel()::count(); }
public static function getNavigationBadgeColor(): ?string { return 'warning'; }
protected static ?string $navigationBadgeTooltip = 'Number of users';
```

### Groups (in PanelProvider)
```php
->navigationGroups([
    NavigationGroup::make()->label('Shop')->icon(Heroicon::OutlinedShoppingCart),
    NavigationGroup::make()->label('Settings')->icon(Heroicon::OutlinedCog6Tooth)->collapsed(),
])
```

### Enum-based Groups
```php
enum NavigationGroup: string { case Shop; case Blog; case Settings; }
// Set on resource: protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Shop;
```

### Custom Navigation Items
```php
->navigationItems([
    NavigationItem::make('Analytics')
        ->url('https://...', shouldOpenInNewTab: true)
        ->icon(Heroicon::OutlinedPresentationChartLine)
        ->group('Reports')
        ->sort(3)
        ->visible(fn() => auth()->user()->can('view-analytics')),
])
```

### Advanced (NavigationBuilder)
```php
->navigation(function (NavigationBuilder $builder): NavigationBuilder {
    return $builder->groups([
        NavigationGroup::make('Website')->items([
            ...PageResource::getNavigationItems(),
            ...Settings::getNavigationItems(),
        ]),
    ]);
})
```

### Sidebar
```php
->sidebarCollapsibleOnDesktop()
->sidebarFullyCollapsibleOnDesktop()
->topNavigation()
->sidebarWidth('40rem')
->collapsedSidebarWidth('9rem')
->breadcrumbs(false)
```

### Reload Sidebar/Topbar
```php
$this->dispatch('refresh-sidebar');
// JS: window.dispatchEvent(new CustomEvent('refresh-sidebar'));
```
