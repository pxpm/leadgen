# Livewire v4 Quickstart

> Source: https://livewire.laravel.com/docs/4.x/quickstart

## 1. Install
```bash
composer require livewire/livewire
```

## 2. Create Layout
```bash
php artisan livewire:layout
```
Generates `resources/views/layouts/app.blade.php` with `@livewireStyles`, `@livewireScripts`, `{{ $slot }}`.

## 3. Create Component
```bash
php artisan make:livewire pages::post.create
```
Creates `resources/views/pages/post/⚡create.blade.php`.

## 4. Write Component
```php
<?php
use Livewire\Component;
new class extends Component {
    public string $title = '';
    public string $content = '';

    public function save() {
        $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);
        Post::create($this->only(['title', 'content']));
        return $this->redirect('/posts');
    }
}; ?>
<form wire:submit="save">
    <input type="text" wire:model="title">
    @error('title') <span style="color:red">{{ $message }}</span> @enderror
    <textarea wire:model="content" rows="5"></textarea>
    @error('content') <span style="color:red">{{ $message }}</span> @enderror
    <button type="submit">Save Post</button>
</form>
```

## 5. Register Route
```php
Route::livewire('/post/create', 'pages::post.create');
```

## Key Concepts
- `wire:model="title"` — two-way binding (defers until action)
- `wire:submit="save"` — calls `save()` on form submit
- `@error('title')` — displays validation errors
- Components MUST have exactly ONE root HTML element
