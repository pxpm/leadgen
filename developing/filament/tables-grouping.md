# Table Grouping (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/tables/grouping
```php
$table->groups([
    Group::make('status'),
    Group::make('author.name')->label('Author'),
    Group::make('created_at')->date(),
])
```
`->defaultGroup('status')` `->groupsOnly(false)` for ungrouped view.
Custom query: `Group::make('author.name')->getGroupQueryUsing(fn($q)=>$q->groupBy('author_id'))`
Collapsible: `Group::make('status')->collapsible()`
