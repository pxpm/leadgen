# Action Modals (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/actions/modals
### Basic
`Action::make('delete')->requiresConfirmation()->modalHeading('Delete')->modalDescription('Sure?')->modalSubmitActionLabel('Yes')`
### With Form
`->schema([TextInput::make('subject')])->action(fn(array $data)=>...)`
### Customization
`->modalWidth('lg')` `->slideOver()` `->modalAlignment(Alignment::Center)` `->modalIcon('heroicon-o-info')` `->modalIconColor('danger')` `->closeModalByClickingAway(false)` `->closeModalByEscaping(false)`
### Footer Actions
`->modalFooterActions([Action::make('cancel')->cancel()])` `->modalSubmitAction(Action::make('send'))`
### Wizards: `->steps([Step::make('Details')->schema([...]),Step::make('Review')])`
