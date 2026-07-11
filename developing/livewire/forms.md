# Livewire v4 Forms

> Source: https://livewire.laravel.com/docs/4.x/forms

## Basic Form
```php
public string $title = '';
public string $content = '';

public function save() {
    Post::create($this->only(['title', 'content']));
    return $this->redirect('/posts');
}
```
```blade
<form wire:submit="save">
    <input type="text" wire:model="title">
    <textarea wire:model="content"></textarea>
    <button type="submit">Save</button>
</form>
```
- Submit buttons auto-disabled during submission
- Form inputs auto-marked `readonly` during submission

## Live Updating Fields
```blade
<input wire:model.live="title">          <!-- immediate -->
<input wire:model.live.blur="title">     <!-- on blur -->
<input wire:model.live.debounce.150ms="title"> <!-- debounced -->
<input wire:model.live.throttle.150ms="title"> <!-- throttled -->
```

## Validation
```php
#[Validate('required|min:5')]
public string $title = '';
```
```blade
@error('title') <span class="error">{{ $message }}</span> @enderror
```

## Form Objects
```bash
php artisan livewire:form PostForm
```
```php
class PostForm extends Form {
    #[Validate('required|min:5')]
    public string $title = '';
    public string $content = '';
}
```
```php
// In component:
public PostForm $form;
```
```blade
<input wire:model="form.title">
@error('form.title') {{ $message }} @enderror
```

## Real-time Form Saving
```php
public function updated($name, $value) {
    $this->post->update([$name => $value]);
}
```

## Dirty Indicators
```blade
<input wire:model.live.blur="title" wire:dirty.class="border-yellow">
<div wire:dirty wire:target="title">Unsaved...</div>
```

## Custom Form Controls
Use `x-modelable="property"` + `{{ $attributes }}` in Blade component:
```blade
<!-- resources/views/components/input-counter.blade.php -->
<div x-data="{ count: 0 }" x-modelable="count" {{ $attributes }}>
    <button @click="count--">-</button>
    <span x-text="count"></span>
    <button @click="count++">+</button>
</div>
```
```blade
<x-input-counter wire:model="quantity" />
```
