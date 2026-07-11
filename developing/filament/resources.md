# Resources Overview (Filament v5)

**URL:** https://filamentphp.com/docs/5.x/resources/overview

## Creating a Resource

```bash
php artisan make:filament-resource Customer
php artisan make:filament-resource Customer --simple     # Modal-based CRUD
php artisan make:filament-resource Customer --generate   # Auto-generate forms/tables
php artisan make:filament-resource Customer --soft-deletes
php artisan make:filament-resource Customer --view       # Include View page
php artisan make:filament-resource Customer --model --migration --factory
```

Structure: `CustomerResource.php`, `Pages/CreateCustomer.php`, `Pages/EditCustomer.php`, `Pages/ListCustomers.php`, `Schemas/CustomerForm.php`, `Tables/CustomersTable.php`.

## Form & Table

```php
public static function form(Schema $schema): Schema
{
    return CustomerForm::configure($schema);
}

public static function table(Table $table): Table
{
    return CustomersTable::configure($table);
}
```

Hiding per operation: `->hiddenOn(Operation::Edit)`, `->visibleOn(Operation::Create)`.

## Model Labels

```php
protected static ?string $modelLabel = 'cliente';
protected static ?string $pluralModelLabel = 'clientes';
protected static bool $hasTitleCaseModelLabel = false;
```

## Navigation

```php
protected static ?string $navigationLabel = 'My Customers';
protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
protected static string|UnitEnum|null $navigationGroup = 'Shop';
protected static ?string $navigationParentItem = 'Products';
protected static ?int $navigationSort = 2;
```

## URL Generation

```php
CustomerResource::getUrl();                                    // /admin/customers
CustomerResource::getUrl('create');                            // /admin/customers/create
CustomerResource::getUrl('edit', ['record' => $customer]);     // /admin/customers/edit/1
CustomerResource::getUrl(panel: 'marketing');                  // cross-panel
```

## Eloquent Query

```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->where('is_active', true);
}
```

## Authorization (Model Policies)

Auto-checked: `viewAny()`, `create()`, `update()`, `view()`, `delete()`, `deleteAny()`, `forceDelete()`, `forceDeleteAny()`, `restore()`, `restoreAny()`, `reorder()`.

```php
protected static bool $shouldSkipAuthorization = true;  // Skip all
```

## Other

- `$recordTitleAttribute = 'name'` for global search.
- `$slug = 'pending-orders'` for custom URL.
- `getRecordSubNavigation()` for sub-navigation between resource pages.
- `mutateFormDataBeforeFill()` to remove sensitive attributes from JS.
