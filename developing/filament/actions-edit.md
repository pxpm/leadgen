# Edit Action (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/actions/edit
`EditAction::make()` auto-detects from resource.
### Custom: `EditAction::make()->record($post)->form([TextInput::make("title")])->mutateFormDataUsing(fn($d)=>$d)->using(fn($r,$d)=>$r->update($d))`
`->successNotificationTitle("Updated")` `->fillForm(fn($r): array => [...])`
