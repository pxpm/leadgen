# Notifications (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/notifications/overview
### Flash
`Notification::make()->title('Saved')->success()->send()` `->danger()` `->warning()` `->info()` `->body('Details')` `->icon('heroicon-o-check')` `->actions([Action::make('undo')])`
### Database
`->sendToDatabase($users)` `->broadcast($users)` stored in notifications table.
### Broadcast
`->broadcast($users)` via websockets. Requires Laravel Echo setup.
