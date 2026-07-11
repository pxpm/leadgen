# Table Layout (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/tables/layout
### Layout Modes
`$table->layout(Layout::Grid)` `$table->layout(Layout::Split)` with record form
### Column Manager
`->reorderableColumns()` `->deferColumnManager(false)` `->columnManagerLayout(ColumnManagerLayout::Modal)` `->columnManagerColumns(2)` `->persistColumnsInSession(false)` `->columnManagerTriggerAction(fn($a)=>$a->button())` `->columnManagerResetActionPosition(ColumnManagerResetActionPosition::Footer)`
### Responsive
Toggle non-essential columns off by default: `->toggleable(isToggledHiddenByDefault:true)`
