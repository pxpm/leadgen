# Creating Records (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/creating-records

## Lifecycle Hooks
`beforeFill()` `afterFill()` `beforeValidate()` `afterValidate()` `beforeCreate()` `afterCreate()`

## Mutate Data
`mutateFormDataBeforeCreate(array $data): array` — modify before save
`handleRecordCreation(array $data): Model` — customize creation entirely

## Redirects
`getRedirectUrl(): string` — `$this->getResource()::getUrl('index')`
Panel-level: `->resourceCreatePageRedirect('index')`

## Notifications
`getCreatedNotificationTitle(): ?string` `getCreatedNotification(): ?Notification`
Return `null` to disable.

## Create Another
`$canCreateAnother = false` to disable
`preserveFormDataWhenCreatingAnother(array $data): array` — `Arr::only($data,['is_admin'])`

## Wizard
`use CreateRecord\Concerns\HasWizard;`
`getSteps(): array` — `[Step::make('Name')->schema([...]), Step::make('Desc')->schema([...])]`
`hasSkippableSteps(): true`

## Import
`ImportAction::make()->importer(ProductImporter::class)` in `getHeaderActions()` of List page.

## Actions
`getHeaderActions(): array` `getFormActions(): array`
`$this->getCreateFormAction()->formId('form')` — move to header

## Halt
`$this->halt()` from any lifecycle hook to stop the process.
