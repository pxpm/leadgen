# Global Search (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/global-search
Set `$recordTitleAttribute = "name"` on resource. Requires Edit or View page.
### Customize
`getGlobalSearchResultTitle(Model $r): string` `getGloballySearchableAttributes(): array` `["title","slug","author.name"]`
`getGlobalSearchResultDetails(Model $r): array` `["Author"=>$r->author->name]`
`getGlobalSearchEloquentQuery(): Builder` eager load
`getGlobalSearchResultActions(Model $r): array` actions on results
`getGlobalSearchResultUrl(Model $r): string` custom URL
