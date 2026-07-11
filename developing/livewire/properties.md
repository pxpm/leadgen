# Livewire v4 Properties

> Source: https://livewire.laravel.com/docs/4.x/properties

## Initializing
```php
public string $title = '';
public array $todos = [];
public ?string $filter = null;

public function mount() {
    $this->todos = ['Buy groceries', 'Walk the dog'];
}
```

## Bulk Assignment
```php
$this->fill($post->only('title', 'description'));
```

## Data Binding
- `wire:model="title"` — defers update until next action (save, click, etc.)
- `wire:model.live="title"` — sends immediately on change
- `wire:model.blur="title"` — sends on blur only
- `wire:model.live.blur="title"` — blur + immediate network request

## Nesting / Dot Notation
```blade
<input wire:model="data.general.timezone">
<input wire:model="address.city">
<input wire:model="items.0.name">
```

## Reset / Pull
```php
$this->reset('title');              // reset to initial value
$this->reset(['title', 'content']); // reset multiple
$this->pull('title');               // reset + return value
$this->pull();                      // reset + return all
```

## Supported Types
- Primitives: `string`, `int`, `float`, `bool`, `array`, `null`
- PHP: `Collection`, `Eloquent\Collection`, `Model`, `DateTime`, `Carbon`, `Stringable`, `BackedEnum`
- Custom: implement `Wireable` interface

## Security
- `#[Locked]` — prevents client-side manipulation
- Public properties = user input → always validate/authorize
- Eloquent model IDs are auto-locked

## $wire in JavaScript
```js
$wire.count              // access property
$wire.count = 5          // mutate (no network request)
$wire.set('count', 5)    // mutate + network request
$wire.set('count', 5, false) // mutate, defer network
```
