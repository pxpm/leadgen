# Editing Records (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/editing-records

## Lifecycle Hooks
`beforeFill()` `afterFill()` `beforeValidate()` `afterValidate()` `beforeSave()` `afterSave()`

## Mutate Data
`mutateFormDataBeforeFill(array $data): array` — modify before form fills
`mutateFormDataBeforeSave(array $data): array` — modify before DB save
`handleRecordUpdate(Model $record, array $data): Model` — customize save entirely

## Redirects
`getRedirectUrl(): string` — `$this->getResource()::getUrl('index')` or `getUrl('view',['record'=>$this->getRecord()])`
Panel: `->resourceEditPageRedirect('index')`

## Notifications
`getSavedNotificationTitle(): ?string` `getSavedNotification(): ?Notification`

## Save Part of Form
```php
Section::make('Settings')->footerActions([
    fn(string $operation): Action => Action::make('save')
        ->action(function (Section $component, EditRecord $livewire) {
            $livewire->saveFormComponentOnly($component);
        })->visible($operation === 'edit'),
])
```

## Multiple Edit Pages
`php artisan make:filament-page EditCustomerContact --resource=CustomerResource --type=EditRecord`
Register: `'edit-contact' => Pages\EditCustomerContact::route('/{record}/edit/contact')`

## Actions
`getHeaderActions()` `getFormActions()`
`$this->getSaveFormAction()->formId('form')` — move to header

## Halt
`$this->halt()` from any lifecycle hook.
