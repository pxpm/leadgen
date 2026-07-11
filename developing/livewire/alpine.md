# Livewire v4 Alpine Integration

> Source: https://livewire.laravel.com/docs/4.x/alpine

## $wire Magic Object
```js
// Access properties
$wire.content.length

// Mutate properties (no network request)
$wire.title = ''

// Mutate + network request
$wire.set('title', 'New Value')

// Deferred mutate (no network)
$wire.set('title', 'New Value', false)

// Call methods
$wire.save()
$wire.deletePost({{ $post->id }})

// Refresh component
$wire.$refresh()
```

## Common Patterns
```blade
<!-- Show/hide with Alpine -->
<div x-data="{ expanded: false }">
    <button @click="expanded = !expanded">Toggle</button>
    <div x-show="expanded">{{ $post->content }}</div>
</div>

<!-- Live character count -->
<input wire:model="content">
<span x-text="$wire.content.length"></span>

<!-- Clear input -->
<button @click="$wire.title = ''">Clear</button>

<!-- Call Livewire method from Alpine -->
<input @blur="$wire.save()">
```

## Blade Parameter Gotcha
```blade
<!-- WRONG - UUIDs need quotes -->
<button @click="$wire.deletePost({{ $post->uuid }})">

<!-- CORRECT -->
<button @click="$wire.deletePost('{{ $post->uuid }}')">
```

## Entangle (Discouraged)
```js
// Prefer direct $wire access instead
x-data="{ open: $wire.entangle('showDropdown') }"
x-data="{ open: $wire.entangle('showDropdown').live }"
```

## @js Directive
```blade
<div x-data="{ posts: @js($posts) }">...</div>
```

## Manual Bundling
```js
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
Alpine.directive('clipboard', (el) => { ... });
Livewire.start();
```
Use `@livewireScriptConfig` in layout (not `@livewireScripts`).
