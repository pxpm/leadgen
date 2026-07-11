# Infolists (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/infolists/overview
Read-only description lists. Entries: `TextEntry IconEntry ImageEntry ColorEntry CodeEntry KeyValueEntry RepeatableEntry`
```php
$infolist->components([
    Section::make('Profile')->schema([
        TextEntry::make('name'),
        TextEntry::make('email')->icon('heroicon-o-envelope'),
        TextEntry::make('created_at')->dateTime(),
    ]),
]);
```
`TextEntry::make('name')->label('Full Name')->default('N/A')->hidden(fn($r)=>!$r->show)` `->url(fn($r)=>route('edit',$r))->openUrlInNewTab()` `->icon('heroicon-o-user')` `->copyable()` `->limit(50)` `->date()` `->dateTime()` `->money('EUR')` `->html()` `->markdown()` `->badge()->color('success')` `->color('danger')` `->weight(FontWeight::Bold)` `->size(TextSize::Large)`
### Utility: $state $record $livewire $component $get $operation
