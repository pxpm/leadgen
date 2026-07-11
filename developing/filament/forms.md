# Forms Overview (Filament v5)

**URL:** https://filamentphp.com/docs/5.x/forms/overview

## Key Takeaways

- Forms are built via `Filament\Forms\Components` classes using `make('name')`.
- Dot notation (`socials.github_url`) binds to nested arrays.
- Validation uses chainable methods: `required()`, `maxLength(255)`, `email()`, `numeric()`.
- Labels auto-generated from field name; override with `label()`, hide visually with `hiddenLabel()`.
- `default('value')` sets initial value (only on Create, not Edit).
- `disabled()` prevents editing; `disabledOn('edit')` for operation-specific. `saved()` saves disabled fields.
- `hidden()` / `visible()` with closures; `hiddenJs()` / `visibleJs()` for client-side without network round-trip.
- `hiddenOn('edit')` / `visibleOn('create')` for operation-based visibility.
- `inlineLabel()` displays label beside field; can be set on Section or entire Schema.
- `autofocus()`, `placeholder()`.

## Field Utility Injection

Functions accept injected parameters:
- `$state` - current field value
- `$get('field')` - get another field's value; typed: `$get->string('email')`
- `$set('field', value)` - set another field's value
- `$record` - current Eloquent model
- `$operation` - 'create', 'edit', or 'view'
- `$livewire` - Livewire component instance
- `$component` - current field instance

## Reactivity

- `live()` - re-render schema on every interaction.
- `live(onBlur: true)` - re-render only on blur (better for text inputs).
- `live(debounce: 500)` - wait 500ms after last keystroke.

## Field Lifecycle

- **Hydration:** `afterStateHydrated()`, `formatStateUsing()` - transform data when loading.
- **Updates:** `afterStateUpdated()` - runs after user changes value.
- **Dehydration:** `dehydrateStateUsing()` - transform before save. `saved(false)` - skip saving.
- **Rendering:** `partiallyRenderAfterStateUpdated()` - only re-render this field. `skipRenderAfterStateUpdated()` + `afterStateUpdatedJs()` - client-side update without network request.

## Saving to Relationships

- Layout components: `->relationship('metadata')` on Section/Group/Fieldset.
- BelongsTo/MorphTo foreign keys must be `nullable()` (schema saved first, then relationship).
- `condition:` parameter for conditional save.
- `saveRelationshipsWhenHidden()` to save even when hidden.

## Global Settings

```php
Checkbox::configureUsing(function (Checkbox $checkbox): void {
    $checkbox->inline(false);
});
```

## Reactive Cookbook

- Conditional hide: `->hidden(fn (Get $get) => !$get('is_company'))`
- Conditional required: `->required(fn (Get $get) => filled($get('company_name')))`
- Slug generation: `->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))`
- Dependant selects: `->options(fn (Get $get) => match($get('category')) {...})`
- Dynamic fields: `->schema(fn (Get $get) => match($get('type')) {...})` on layout component
- Auto-hash password: `->dehydrateStateUsing(fn (string $state) => Hash::make($state))->saved(fn (?string $state) => filled($state))`
