# Schemas (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/schemas/overview

Schemas are the foundation of Filament SDUI. Build UIs declaratively via PHP objects.

## Component Types
- Form fields: TextInput, Select, Checkbox, Toggle, DatePicker...
- Infolist entries: TextEntry, IconEntry, ImageEntry...
- Layout: Grid, Flex, Fieldset, Section, Tabs, Wizard
- Prime: Text, Icon, Image, UnorderedList
- Actions: buttons that trigger logic/modals

## Structure
```php
$schema->components([
    Grid::make(2)->schema([
        Section::make('Details')->schema([
            TextInput::make('name'),
            Select::make('position')->options([...]),
        ]),
    ]),
]);
```

## Utility Injection
`$get('field')` `$record` `$operation` `$livewire` `$component`

## Global
`Section::configureUsing(fn(Section $s)=>$s->columns(2))`

## Security
Use `RestrictsFileUploadsToSchemaComponents` trait on public Livewire components.
