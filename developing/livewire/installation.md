# Livewire v4 Installation

> Source: https://livewire.laravel.com/docs/4.x/installation

## Prerequisites
- Laravel 10+
- PHP 8.1+

## Install
```bash
composer require livewire/livewire
```
Auto-discovery, no extra setup.

## Layout File
```bash
php artisan livewire:layout
```
Creates `resources/views/layouts/app.blade.php` with `@livewireStyles` + `@livewireScripts` + `{{ $slot }}`.

## Config
```bash
php artisan livewire:config
```
Publishes `config/livewire.php`.

## Advanced: Manual Bundling
Use `@livewireScriptConfig` (instead of `@livewireScripts`) and import in JS:
```js
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
Alpine.plugin(MyPlugin);
Livewire.start();
```

## Advanced: Custom Update Endpoint
```php
Livewire::setUpdateRoute(function ($handle, $path) {
    return Route::post('/custom'.$path, $handle)->middleware(['web','auth']);
});
```
