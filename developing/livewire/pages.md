# Livewire v4 Pages

> Source: https://livewire.laravel.com/docs/4.x/pages

## Routing
```php
Route::livewire('/posts/create', 'pages::post.create');
Route::livewire('/posts/{post}', 'pages::post.show');  // route model binding
```

## Layouts
Default: `resources/views/layouts/app.blade.php`

```blade
<html>
<head>@livewireStyles</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
```

### Custom Layouts
```php
use Livewire\Attributes\Layout;

#[Layout('layouts::dashboard')]
class extends Component { ... }
```
Or via render:
```php
public function render() {
    return $this->view()->layout('layouts::dashboard');
}
```

## Page Title
```php
use Livewire\Attributes\Title;

#[Title('Create Post')]
class extends Component { ... }
```
Layout needs: `<title>{{ $title ?? config('app.name') }}</title>`

## Named Slots for Layout
```blade
<!-- Layout: -->
<html lang="{{ $lang ?? app()->getLocale() }}">
```
```blade
<!-- Component view (outside root element): -->
<x-slot:lang>fr</x-slot>
<div>French content...</div>
```

## Route Parameters
```php
Route::livewire('/posts/{id}', 'pages::show-post');

public function mount($id) {
    $this->post = Post::findOrFail($id);
}
```

## Route Model Binding
```php
Route::livewire('/posts/{post}', 'pages::show-post');

// Just declare the property - Livewire auto-binds:
public Post $post;
```
