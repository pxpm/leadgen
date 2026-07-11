# Deleting Records (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/deleting-records
`php artisan make:filament-resource Customer --soft-deletes`
### Soft Deletes
Table: `TrashedFilter::make()` `DeleteAction::make()` `ForceDeleteAction::make()` `RestoreAction::make()`
Bulk: `DeleteBulkAction::make()` `ForceDeleteBulkAction::make()` `RestoreBulkAction::make()`
Query: `getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class])`
### Auth: `delete()` single, `deleteAny()` bulk, `forceDelete()`/`forceDeleteAny()`, `restore()`/`restoreAny()`
Use `->authorizeIndividualRecords()` on DeleteBulkAction for per-record checks.
