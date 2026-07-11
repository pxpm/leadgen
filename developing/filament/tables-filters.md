# Table Filters (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/tables/filters/overview

## Basic
`Filter::make('is_featured')->query(fn($q)=>$q->where('is_featured',true))->label('F')`

## Toggle
`Filter::make('is_featured')->toggle()`

## Modify Field
`->modifyFormFieldUsing(fn(Checkbox $f)=>$f->inline(false))`

## Filter Types
Filter (checkbox) SelectFilter (dropdown) TernaryFilter (true/false/blank) TrashedFilter (soft deletes) Custom filters

## Table Config
`->deferFilters(false)` live `->persistFiltersInSession()` `->deselectAllRecordsWhenFiltered(false)` `->filtersApplyAction(fn($a)=>$a->link())` `->filtersTriggerAction(fn($a)=>$a->button())`

## Advanced
`->baseQuery(fn($q)=>$q->withoutGlobalScopes([...]))` `->excludeWhenResolvingRecord()` `->default()`

## Utility: $filter $livewire $table
