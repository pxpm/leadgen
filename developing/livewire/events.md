# Livewire v4 Events

> Source: https://livewire.laravel.com/docs/4.x/events

## Dispatching
```php
$this->dispatch('post-created');
$this->dispatch('post-created', title: $post->title);
$this->dispatch('post-created')->to(Dashboard::class);   // to specific component
$this->dispatch('post-created')->to(self: true);          // to self only
```

## Listening
```php
use Livewire\Attributes\On;

#[On('post-created')]
public function updatePostList($title) { ... }
```

## Dynamic Event Names
```php
$this->dispatch("post-updated.{$post->id}");

#[On('post-updated.{post.id}')]
public function refreshPost() { ... }
```

## Listening from Child Components (Blade)
```blade
<livewire:edit-post @saved="$refresh">
<livewire:edit-post @saved="close($event.detail.postId)">
```

## JavaScript Interactions
```js
// Inside component <script>:
this.$dispatch('post-created');
this.$dispatchSelf('post-created');
this.$on('post-created', (event) => { ... });

// Global:
document.addEventListener('livewire:init', () => {
    Livewire.on('post-created', (event) => { ... });
});
```

## Alpine Integration
```blade
<!-- Listening -->
<div x-on:post-created="..."></div>
<div x-on:post-created.window="notify('New post: ' + $event.detail.title)"></div>

<!-- Dispatching -->
<button x-on:click="$dispatch('post-created', { title: 'Post Title' })">...</button>
```

## Testing
```php
->assertDispatched('post-created')
->assertNotDispatched('post-created')
->dispatch('post-created')  // dispatch from test
```

## Laravel Echo
```php
#[On('echo:orders,OrderShipped')]
public function notifyNewOrder() { ... }

// Private channels:
"echo-private:orders,OrderShipped" => 'notifyNewOrder'
"echo-presence:orders,OrderShipped" => 'notifyNewOrder'
```
