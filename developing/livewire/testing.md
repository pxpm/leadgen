# Livewire v4 Testing

> Source: https://livewire.laravel.com/docs/4.x/testing

## Install Pest (Recommended)
```bash
composer remove phpunit/phpunit
composer require pestphp/pest --dev --with-all-dependencies
./vendor/bin/pest --init
```

## Basic Test
```php
it('renders successfully', function () {
    Livewire::test('post.create')->assertStatus(200);
});
```

## Setting Properties
```php
->set('title', 'My amazing post')
->set(['title' => '...', 'content' => '...'])
->toggle('sortAsc')
```

## Calling Actions
```php
->call('save')
->call('deletePost', $postId)
```

## Assertions
```php
->assertSet('title', 'expected')
->assertNotSet('title', 'not-expected')
->assertCount('posts', 3)
->assertSee('Some text')
->assertDontSee('Hidden text')
->assertSeeInOrder(['first', 'second'])
->assertViewHas('posts')
->assertViewHas('postCount', 3)
->assertViewIs('livewire.show-posts')
```

## Events
```php
->assertDispatched('post-created')
->assertDispatched('notify', message: 'Post deleted')
->assertNotDispatched('post-created')
->dispatch('post-created')  // dispatch from test
```

## Validation
```php
->assertHasErrors('title')
->assertHasErrors(['title' => ['required', 'min:6']])
->assertHasNoErrors('title')
```

## Redirects
```php
->assertRedirect('/posts')
->assertRedirectToRoute('posts.index')
->assertNoRedirect()
```

## Authorization
```php
->assertUnauthorized()  // 401
->assertForbidden()     // 403
->assertStatus(500)     // custom status
```

## Authentication
```php
Livewire::actingAs($user)->test('show-posts')...
```

## URL Params & Cookies
```php
Livewire::withQueryParams(['search' => 'Laravel'])->test('search-posts')...
Livewire::withCookies(['discountToken' => 'SUMMER2024'])->test('cart')...
```

## Browser Tests
```php
Livewire::visit('post.create')
    ->type('[wire\:model="title"]', 'My first post')
    ->press('Save')
    ->assertSee('Post created successfully');
```
