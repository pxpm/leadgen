# Livewire v4 Components

> Source: https://livewire.laravel.com/docs/4.x/components

## Creating Components
```bash
php artisan make:livewire post.create          # single-file (default)
php artisan make:livewire post.create --mfc    # multi-file
php artisan make:livewire CreatePost --class   # class-based
php artisan make:livewire pages::post.create   # page component
```

## Formats

| Format | Path | Use |
|--------|------|-----|
| Single-file | `resources/views/components/post/⚡create.blade.php` | Most components |
| Multi-file | `resources/views/components/post/⚡create/create.php` + `.blade.php` + `.js` + `.css` | Complex components |
| Class-based | `app/Livewire/CreatePost.php` + `resources/views/livewire/create-post.blade.php` | v3 migration |

## Rendering
```blade
<livewire:post.create />
<livewire:post.create :title="$initialTitle" />   <!-- dynamic prop -->
<livewire:pages::post.create />                    <!-- namespaced -->
```

## Passing Props
```blade
<livewire:post.create title="Static Title" />
<livewire:post.create :title="$dynamicTitle" />
<livewire:post.create :$title />   <!-- shorthand when name matches -->
```

Received via `mount()`:
```php
public function mount($title = null) { $this->title = $title; }
// Or omit mount() entirely — Livewire auto-assigns matching property names
```

## Page Components
```php
Route::livewire('/posts/create', 'pages::post.create');
// Route model binding:
Route::livewire('/posts/{post}', 'pages::post.show');
// $post auto-bound to Post model
```

## Accessing Data in Views
- Public properties: `{{ $title }}`
- Protected: `{{ $this->apiKey }}` (never sent to client)
- Computed: `{{ $this->posts }}` (requires `#[Computed]` attribute)
- Render data: `return $this->view(['author' => Auth::user()]);`

## Organizing
- `pages::` → `resources/views/pages/`
- `layouts::` → `resources/views/layouts/`
- Custom namespaces in `config/livewire.php`

## Key Gotchas
- Components MUST have exactly ONE root element
- ⚡ emoji in filename is optional (disable in config)
- `mount()` runs only once per component lifecycle
