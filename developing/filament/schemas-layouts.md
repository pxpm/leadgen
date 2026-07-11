# Schema Layouts (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/schemas/layouts
### Grid: `Grid::make(2)->schema([...])` `Grid::make(['lg'=>3,'md'=>2,'sm'=>1])`
### Flex: `Flex::make([Icon::make(...),Text::make(...)])` for inline layouts
### Fieldset: `Fieldset::make('Meta')->relationship('metadata')->schema([...])`
### Column Span: `->columnSpan(2)` `->columnSpanFull()`
### Grid System: Grid > Section/Fieldset > fields. Nest infinitely.
