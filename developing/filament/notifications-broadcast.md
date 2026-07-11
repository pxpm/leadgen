# Broadcast Notifications (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/notifications/broadcast-notifications
Real-time via websockets. Requires Laravel Echo setup (Pusher/Ably/Soketi).
### Send: `Notification::make()->title("New Order")->broadcast($users)`
### Receive: User model needs `ReceivesBroadcastNotificationsOnSocket` trait. Bell updates in real-time.
### Echo setup: configure `config/broadcasting.php`, set up `laravel-echo` on frontend.
