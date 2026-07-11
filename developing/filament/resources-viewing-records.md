# Viewing Records (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/viewing-records
```bash
php artisan make:filament-resource User --view
php artisan make:filament-page ViewUser --resource=UserResource --type=ViewRecord
```
Register: `"view" => Pages\ViewUser::route("/{record}")`
### Infolist (preferred over disabled form)
`public static function infolist(Schema $schema): Schema { return $schema->components([TextEntry::make("name"), TextEntry::make("email")]); }`
### View in modal: `->recordActions([ViewAction::make()])` in table
### Mutate: `mutateFormDataBeforeFill(array $data): array`
### Multiple View pages: register in getPages() with unique keys, override infolist()/form()
### Auth: policy `view()` method
### Custom content: override `content(Schema $schema)` or set `protected string $view`
