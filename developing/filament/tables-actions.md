# Table Actions (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/tables/actions
### Record Actions (per row)
`->recordActions([EditAction::make(),Action::make('feature')->action(...)])`
### Toolbar Actions
`->toolbarActions([CreateAction::make(),BulkActionGroup::make([DeleteBulkAction::make()])])`
### Header Actions
`->headerActions([Action::make('export')->icon('heroicon-o-arrow-down-tray')])`
### Bulk Actions
`BulkActionGroup::make([DeleteBulkAction::make()->authorizeIndividualRecords(),ExportBulkAction::make()])`
### Positioning
Actions before/after cells, in groups/dropdowns. `->extraAttributes()` for custom styling.
