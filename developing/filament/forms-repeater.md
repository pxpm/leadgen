# Forms Repeater (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/forms/repeater
`Repeater::make('items')->schema([TextInput::make('name'),TextInput::make('qty')->numeric()])` `->addActionLabel('Add Item')` `->minItems(1)` `->maxItems(5)` `->reorderable()` `->collapsible()` `->cloneable()` `->grid(2)` `->defaultItems(1)` `->itemLabel(fn($state)=>$state['name']??'New')` `->relationship('items')` for HasMany.
### Custom: `->mutateRelationshipDataBeforeFillUsing(fn($data)=>...)` `->mutateRelationshipDataBeforeSaveUsing(fn($data)=>...)`
