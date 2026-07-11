# Livewire v4 Validation

> Source: https://livewire.laravel.com/docs/4.x/validation

## #[Validate] Attribute (Recommended)
```php
use Livewire\Attributes\Validate;

#[Validate('required|min:3')]
public string $title = '';

#[Validate('required|min:3', as: 'date of birth')]
public string $dob = '';

#[Validate('required', message: 'Please provide a post title')]
public string $title = '';
```

## Manual validate()
Always call `$this->validate()` before persisting:
```php
public function save() {
    $validated = $this->validate();
    Post::create($validated);
}
```

## rules() Method (for Rule objects)
```php
protected function rules() {
    return [
        'title' => ['required', Rule::unique('posts')->ignore($this->post)],
        'content' => 'required|min:5',
    ];
}
```
- Only validates when `$this->validate()` is called
- Add empty `#[Validate]` on property for real-time validation with `rules()` method

## Real-time Validation
```blade
<input wire:model.live.blur="title">
@error('title') {{ $message }} @enderror
```
Rules run automatically when using `#[Validate]` + `wire:model.live`.

## Form Object Validation
```php
class PostForm extends Form {
    #[Validate('required|min:5')]
    public string $title = '';
}
// In component:
public PostForm $form;
// In blade:
wire:model="form.title"
@error('form.title') ...
```

## Manual Error Management
```php
$this->addError('title', 'Custom error message');
$this->resetValidation('title');
$this->resetValidation(); // all errors
```

## Testing
```php
->assertHasErrors('title')
->assertHasErrors(['title' => ['min:3']])
->assertHasNoErrors('title')
```
