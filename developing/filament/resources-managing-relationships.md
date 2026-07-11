# Managing Relationships (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/managing-relationships

## Four Tools for Relationships
- **Relation Managers** (HasMany/BelongsToMany/MorphMany): interactive tables below Edit/View forms
- **Select/CheckboxList** (BelongsTo/MorphTo/BelongsToMany): choose existing or create new in modal
- **Repeaters** (HasMany/MorphMany): inline CRUD inside the main form (good for few fields)
- **Layout relationship()** (BelongsTo/HasOne/MorphOne): save fields to related model via Section/Fieldset/Group

## Relation Manager
`php artisan make:filament-relation-manager CategoryResource posts title`
Options: `--attach` `--associate` `--view` `--soft-deletes`
Register in `getRelations(): array` on resource.

## Attach/Detach (BelongsToMany)
`AttachAction::make()->multiple()->preloadRecordSelect()->recordSelectOptionsQuery(fn($q)=>$q->whereBelongsTo(auth()->user()))`
`DetachAction::make()` `DetachBulkAction::make()`
Pivot attrs: `$action->getRecordSelect(); Forms\Components\TextInput::make('role')` with `withPivot()`

## Associate/Dissociate (HasMany)
`AssociateAction::make()->preloadRecordSelect()` `DissociateAction::make()`

## Owner Record Access
`$this->getOwnerRecord()` or `fn(RelationManager $livewire) => $livewire->getOwnerRecord()`

## Relation Pages
`php artisan make:filament-page ManageAddresses --resource=CustomerResource --type=ManageRelatedRecords`
In `getPages()`: `'addresses' => Pages\ManageCustomerAddresses::route('/{record}/addresses')`

## Other
- `RelationGroup::make('Label',[...])` grouping, `canViewForRecord()` conditional, `$isLazy` disable
- Share resource form/table: `return PostResource::form($schema)` / `PostResource::table($table)`
- `hiddenOn(CommentsRelationManager::class)` to hide fields/columns/filters on relation manager
- Combine with form: `hasCombinedRelationManagerTabsWithContent(): true`
