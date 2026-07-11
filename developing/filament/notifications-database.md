# Database Notifications (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/notifications/database-notifications
Store notifications in DB: `php artisan notifications:table` `php artisan migrate`
### Send: `Notification::make()->title("New")->sendToDatabase($users)` or `->broadcast($users)` for real-time
### Receiving: add `HasDatabaseNotifications` trait to User model. Bell icon in topbar.
### Mark read: `$this->markNotificationsAsRead()` `->markAsRead()` action
### Custom: `getDatabaseNotificationsTable()` for custom table.
