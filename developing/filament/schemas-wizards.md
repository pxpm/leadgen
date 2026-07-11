# Schema Wizards (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/schemas/wizards
`Wizard::make([Step::make('Details')->schema([...]),Step::make('Review')->schema([...])])`
`Step::make()->icon('heroicon-o-user')->skippable()->afterValidation(fn()=>...)`
`Wizard::make()->startOnStep(2)->persistStepInQueryString('step')->submitAction(fn($a)=>$a->label('Finish'))`
