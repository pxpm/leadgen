# Forms Select (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/forms/select
`Select::make('status')->options(['draft'=>'Draft','published'=>'Published'])`
`->searchable()` `->multiple()` `->native(false)` `->createOptionForm([...])` `->createOptionUsing(fn($data)=>...)`
`->relationship('author','name')` `->relationship('author','name',fn($q)=>$q->where('active',true))`
`->getSearchResultsUsing(fn($s)=>...)` `->getOptionLabelUsing(fn($v)=>...)` `->preload()` `->loadingMessage('Loading...')` `->noSearchResultsMessage('None')` `->allowHtml()`
