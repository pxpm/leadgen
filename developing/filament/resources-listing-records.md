# Listing Records (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/listing-records

## Tabs
```php
public function getTabs(): array {
    return [
        'all' => Tab::make(),
        'active' => Tab::make()->modifyQueryUsing(fn($q)=>$q->where('active',true)),
    ];
}
```
`Tab::make('Label')->icon('heroicon-m-user')->badge($count)->badgeColor('success')->deferBadge()`
`getDefaultActiveTab(): string|int|null` — `'active'`
`->excludeQueryWhenResolvingRecord()` — skip tab query when resolving individual records

## Content Schema
```php
public function content(Schema $schema): Schema {
    return $schema->components([
        $this->getTabsContentComponent(),
        RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
        EmbeddedTable::make(),
    ]);
}
```

## Authorization
`viewAny()` policy method controls List page access.

## Custom Eloquent Query
`->modifyQueryUsing(fn(Builder $q)=>$q->withoutGlobalScopes())` in table.
