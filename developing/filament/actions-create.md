# Create Action (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/actions/create
`CreateAction::make()` auto-detects resource model.
### Custom: `CreateAction::make()->model(Post::class)->form([TextInput::make("title")])->mutateFormDataUsing(fn($d)=>$d)->using(fn($d)=>Post::create($d))`
`->createAnother(false)` `->successNotificationTitle("Created")` `->successNotification(fn($r)=>Notification::make())`
