# Delete Action (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/actions/delete
`DeleteAction::make()` `DeleteBulkAction::make()`
### Custom: `DeleteAction::make()->requiresConfirmation()->modalHeading("Delete")->successNotificationTitle("Deleted")->action(fn()=>$this->record->delete())`
`->using(fn($r)=>$r->delete())` `->authorizeIndividualRecords()` on bulk.
