# Livewire v4 Nesting

> Source: https://livewire.laravel.com/docs/4.x/nesting

## Nesting a Component
```blade
<livewire:todos />
```

## Passing Props
```blade
<livewire:todo-count :todos="$this->todos" />
<livewire:todo-count :$todos />                    <!-- shorthand -->
<livewire:todo-count label="Todo Count:" />         <!-- static string -->
<livewire:todo-count inline />                       <!-- boolean true -->
```

## Rendering in Loop — CRITICAL
```blade
@foreach($todos as $todo)
    <livewire:todo-item :$todo :wire:key="$todo->id" />
@endforeach
```
**Keys are mandatory** for child components in loops.

## Reactive Props
```php
use Livewire\Attributes\Reactive;

#[Reactive]
public $todos;
```
By default, props are NOT reactive. Use `#[Reactive]` to auto-update child when parent prop changes.

## Binding to Child (wire:model)
```php
// Child component:
use Livewire\Attributes\Modelable;

#[Modelable]
public string $value = '';
```
```blade
<!-- Parent: -->
<livewire:todo-input wire:model="todo" />
```

## Slots
```blade
<livewire:comment :$comment :wire:key="$comment->id">
    <button wire:click="removeComment({{ $comment->id }})">Remove</button>
</livewire:comment>
```
In child: `{{ $slot }}`, named slots: `{{ $slots['actions'] }}`

## Events from Children
```php
// Child:
$this->dispatch('remove-todo', todoId: $this->todo->id);
// Parent:
#[On('remove-todo')]
public function remove($todoId) { ... }
```
Or client-side dispatch (no server roundtrip):
```blade
<button wire:click="$dispatch('remove-todo', { todoId: {{ $todo->id }} })">Remove</button>
```

## Direct Parent Access
```blade
<button wire:click="$parent.remove({{ $todo->id }})">Remove</button>
```

## Dynamic Components
```blade
<livewire:dynamic-component :is="$current" :wire:key="$current" />
```

## Islands vs Nested Components
- **Islands**: Performance isolation within same component (no separate file)
- **Nested**: True encapsulation, reusable, separate lifecycle
