# Forms Text Input (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/forms/text-input
`TextInput::make('name')->required()->maxLength(255)`
`->password()` `->email()` `->numeric()` `->tel()` `->url()` `->prefix('http://')` `->suffix('.com')`
`->prefixIcon('heroicon-o-globe')` `->suffixIcon('heroicon-o-check')` `->hint('Full name')`
`->autocomplete('name')` `->minValue(1)` `->maxValue(100)` `->step(0.01)` `->mask('999-999-9999')`
`->extraInputAttributes(['data-action'=>'...'])`
