# Livewire v4 Actions

> Source: https://livewire.laravel.com/docs/4.x/actions

## Calling Actions
```blade
<button wire:click="save">Save</button>
<form wire:submit="save">...</form>
<input wire:keydown.enter="search($event.target.value)">
```

## Passing Parameters
```blade
<button wire:click="delete({{ $post->id }})">Delete</button>
```
```php
public function delete($id) { ... }
public function delete(Post $post) { ... } // route model binding
```

## Magic Actions
```blade
$set('property', value)    // set property
$refresh                   // re-render component
$toggle('sortAsc')         // toggle boolean
$dispatch('event-name')    // dispatch event
$parent.methodName()       // call parent method
$event                     // access JS event object
```

## Event Listeners (wire: modifiers)
| Directive | Triggers on |
|-----------|------------|
| `wire:click` | Click |
| `wire:submit` | Form submit |
| `wire:keydown` | Key press |
| `wire:keyup` | Key release |
| `wire:mouseenter` | Mouse enter |
| `wire:*` | Any browser event |

## Event Modifiers
`.prevent` `.stop` `.window` `.outside` `.document` `.once` `.debounce` `.debounce.100ms` `.throttle` `.throttle.100ms` `.self` `.camel` `.dot` `.passive` `.capture`

## Confirming Actions
```blade
<button wire:click="delete" wire:confirm="Are you sure?">Delete</button>
<button wire:click="delete" wire:confirm.prompt="Type DELETE to confirm|DELETE">Delete</button>
```

## Skip Re-render
```php
#[Renderless]
public function incrementViewCount() { ... }
// Or: wire:click.renderless="incrementViewCount"
```

## Async Actions
```php
#[Async]
public function logActivity() { ... }
// Or: wire:click.async="logActivity"
```

## Calling from Alpine
```js
$wire.save()                    // call method
$wire.addTodo(todo)             // with params
await $wire.getPostCount()      // get return value (Promise)
```

## Security
- ALL public methods are callable from client
- Always authorize action parameters
- Mark dangerous methods `protected` or `private`
