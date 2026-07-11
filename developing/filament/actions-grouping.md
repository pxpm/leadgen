# Action Groups (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/actions/grouping-actions
```php
ActionGroup::make([EditAction::make(),ViewAction::make(),DeleteAction::make()])
    ->button()->label('Actions')->icon('heroicon-m-ellipsis-vertical')
    ->color('primary')->size(Size::Small)->tooltip('More')
```
### Styles: `->button()` `->iconButton()` `->link()`
### Placement: `->dropdownPlacement('bottom-end')`
### Nested groups for hierarchical dropdowns.
