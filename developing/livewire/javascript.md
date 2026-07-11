# Livewire v4 JavaScript

> Source: https://livewire.laravel.com/docs/4.x/javascript

## Component Scripts
```blade
<script>
    // Runs every time component loads
    this.$js.increment = () => { console.log('increment') }
    setInterval(() => $wire.$refresh(), 2000)
</script>
```
Class-based components need `@script`/`@endscript` wrapper.

## $wire Object
```js
$wire.count              // get property
$wire.count = 5          // set (no network)
$wire.$set('count', 5)   // set + network
$wire.save()             // call method
$wire.$refresh()         // re-render
$wire.$dispatch('event') // dispatch event
$wire.$on('event', cb)   // listen for event
$wire.$el                // root DOM element
```

## Loading Assets
```blade
@assets
<script src="https://cdn.example.com/lib.js" defer></script>
<link rel="stylesheet" href="...">
@endassets
```
Only loaded once per page, no matter how many component instances.

## Interceptors
```js
// Action level
$wire.intercept('save', ({ onSuccess, onError }) => {
    onSuccess(() => showToast('Saved!'))
    onError(() => showToast('Failed', 'error'))
})

// Message level (per-component)
$wire.interceptMessage(({ onSend, onFinish }) => { ... })

// Request level (HTTP)
Livewire.interceptRequest(({ onError }) => { ... })
```

## Global Events
```js
document.addEventListener('livewire:init', () => { ... })
document.addEventListener('livewire:initialized', () => { ... })
```

## Global Livewire Object
```js
Livewire.first()                    // first component on page
Livewire.find(id)                   // by ID
Livewire.getByName('dashboard')     // by name
Livewire.all()                      // all components
Livewire.dispatch('post-created')   // dispatch event
Livewire.on('post-created', cb)     // listen globally
```

## Server-side JS
```php
$this->js("alert('Post saved!')");
$this->js('$wire.$refresh()');
```

## @js Directive
```blade
<script>let posts = @js($posts);</script>
```
