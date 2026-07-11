# Livewire v4 Lifecycle Hooks

> Source: https://livewire.laravel.com/docs/4.x/lifecycle-hooks

## Hook Order

| Hook | When |
|------|------|
| `mount()` | Component created (ONCE) |
| `hydrate()` | Beginning of subsequent requests |
| `boot()` | Every request (initial + subsequent) |
| `updating()` | Before property update |
| `updated()` | After property update |
| `rendering()` | Before view render |
| `rendered()` | After view render |
| `dehydrate()` | End of every request |
| `exception($e, $stopPropagation)` | When exception thrown |

## Mount
```php
public function mount(Post $post) {
    $this->title = $post->title;
    $this->content = $post->content;
}
```
- Called only ONCE on component initialization
- Receives route params and props
- Use dependency injection in mount params

## Update Hooks
```php
// Generic:
public function updated($property, $value) { ... }

// Specific property:
public function updatedUsername() {
    $this->username = strtolower($this->username);
}

// Arrays get $key param:
public function updatedPreferences($value, $key) { ... }
```

## Trait Prefixing
```php
trait HasPostForm {
    public function mountHasPostForm() { ... }
    public function bootHasPostForm() { ... }
    public function updatingHasPostForm() { ... }
    public function updatedHasPostForm() { ... }
}
```
