# Clusters (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/navigation/clusters

Hierarchical grouping of resources and pages with shared sub-navigation.

## Setup
```php
// Panel: ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
```
`php artisan make:filament-cluster Settings`

## Cluster Class
```php
class SettingsCluster extends Cluster {
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;
    protected static ?string $clusterBreadcrumb = 'settings';
    protected static bool $shouldRegisterSubNavigation = false;
}
```

## Add Resources/Pages
```php
protected static ?string $cluster = SettingsCluster::class;
```

## Recommended Structure
```
Clusters/Settings/SettingsCluster.php
Clusters/Settings/Pages/ManageBranding.php
Clusters/Settings/Resources/ColorResource.php
```

New resources/pages auto-detect cluster directory when generating via make: commands.
