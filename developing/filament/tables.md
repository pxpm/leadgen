# Tables Overview (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/tables/overview

## Defining Tables
```php
$table->columns([TextColumn::make('title'), TextColumn::make('slug')])
    ->filters([Filter::make('is_featured')->query(fn ($q) => $q->where('is_featured', true))])
    ->recordActions([EditAction::make()])
    ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
```

## Pagination
`->paginated([10,25,50,100,'all'])` `->defaultPaginationPageOption(25)` `->extremePaginationLinks()` `->paginationMode(PaginationMode::Simple)` `->paginationMode(PaginationMode::Cursor)` `->queryStringIdentifier('users')` `->paginated(false)`

## Record URLs
`->recordUrl(fn ($r) => route('posts.edit',$r))` `->openRecordUrlInNewTab()`

## Reordering
`->reorderable('sort')` `->paginatedWhileReordering()` `->beforeReordering(fn($order)=>...)`

## Other
`->heading('Title')->description('Desc')` `->poll('10s')` `->deferLoading()` `->striped()` `->recordClasses(fn($r)=>...)` `->searchUsing(fn($q,$s)=>$q->whereKey(Post::search($s)->keys()))`

## Global
`Table::configureUsing(fn(Table $t)=>$t->reorderableColumns())`
