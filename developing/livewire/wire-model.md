# Livewire v4 wire:model

> Source: https://livewire.laravel.com/docs/4.x/wire-model

## Basic
```blade
wire:model="title"
wire:model="address.city"
wire:model="form.title"
```

## Modifiers

| Modifier | Effect |
|----------|--------|
| `.live` | Send updates immediately |
| `.blur` | Update on blur only |
| `.change` | Update on change event (select elements) |
| `.enter` | Update on enter key |
| `.lazy` | v3-compatible: change + network request |
| `.debounce.Xms` | Debounce (use with `.live`) |
| `.throttle.Xms` | Throttle (use with `.live`) |
| `.number` | Cast to int |
| `.boolean` | Cast to bool |
| `.fill` | Use initial value from HTML `value=""` |
| `.deep` | Listen to child element events |
| `.preserve-scroll` | Maintain scroll position |

## Input Types
- Text: `<input wire:model="title">`
- Textarea: `<textarea wire:model="content"></textarea>`
- Checkbox: `<input type="checkbox" wire:model="receiveUpdates">`
- Radio: `<input type="radio" value="yes" wire:model="receiveUpdates">`
- Select: `<select wire:model="state">...`
- Multi-select: `<select wire:model="states" multiple>...`

## Select Dropdowns
```blade
<select wire:model="state">
    <option disabled value="">Select a state...</option>
    @foreach(State::all() as $state)
        <option value="{{ $state->id }}">{{ $state->label }}</option>
    @endforeach
</select>
```
- No need for manual `selected` attribute — Livewire handles it
- For immediate updates on select: `wire:model.live`

## Dependent Selects — CRITICAL
```blade
<select wire:model.live="selectedState">...</select>

<select wire:model.live="selectedCity" wire:key="{{ $selectedState }}">
    @foreach(City::whereStateId($selectedState->id)->get() as $city)
        <option value="{{ $city->id }}">{{ $city->label }}</option>
    @endforeach
</select>
```
**MUST add `wire:key`** to the dependent select to force proper re-rendering.
