# Table Columns (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/tables/columns/overview

## Built-in: TextColumn IconColumn ImageColumn ColorColumn
Editable: SelectColumn ToggleColumn TextInputColumn CheckboxColumn

## Core Methods
`->label('X')` `->state('val')` `->default('fallback')` `->placeholder('...')`
`->sortable()` `->sortable(['first','last'])` `->sortable(query:fn($q,$d)=>...)`
`->searchable()` `->searchable(['first','last'])` `->searchable(isIndividual:true)`

## Clickable
`->url(fn($r)=>route('edit',$r))` `->openUrlInNewTab()` `->action(fn($r)=>$this->dispatch('open'))` `->disabledClick()`

## Relationships
`TextColumn::make('author.name')` dot notation
`->counts('users')` `->exists('users')` `->avg('users','age')` `->sum('users','amount')`

## Styling
`->alignStart()/Center()/End()` `->grow()` `->width('1%')` `->wrapHeader()`
`->tooltip('T')` `->headerTooltip('T')`

## Visibility
`->toggleable()` `->toggleable(isToggledHiddenByDefault:true)`
`->extraAttributes([...])` `->extraCellAttributes([...])`

## Grouping
`ColumnGroup::make('Vis',[TextColumn::make('status'),IconColumn::make('feat')])->alignCenter()`

## Utility: $state $record $rowLoop $livewire $component $table
