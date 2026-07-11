# Livewire v4 Documentation Cache

> Source: https://livewire.laravel.com/docs/4.x/
> Last updated: 2026-06-24

## Index

| File | Topic | Key Takeaways |
|------|-------|---------------|
| [installation.md](installation.md) | Installation | `composer require`, layout via `php artisan livewire:layout`, manual bundling |
| [quickstart.md](quickstart.md) | Quickstart | Create first component, `wire:model`, `wire:submit`, validation |
| [components.md](components.md) | Components | Single-file/multi-file/class-based, rendering, props, pages |
| [properties.md](properties.md) | Properties | `wire:model.live`, types, `#[Locked]`, `$wire` in JS |
| [actions.md](actions.md) | Actions | `wire:click`, `wire:submit`, magic actions, security |
| [forms.md](forms.md) | Forms | Submitting, live-updating, form objects, dirty indicators |
| [validation.md](validation.md) | Validation | `#[Validate]`, `rules()`, real-time, form objects |
| [events.md](events.md) | Events | `dispatch()`, `#[On]`, Alpine integration, Echo |
| [lifecycle-hooks.md](lifecycle-hooks.md) | Lifecycle | `mount()`, `boot()`, `updated()`, `hydrate()`/`dehydrate()` |
| [wire-model.md](wire-model.md) | wire:model | Modifiers, select dropdowns, **dependent selects need `wire:key`** |
| [testing.md](testing.md) | Testing | Pest integration, assertions, `Livewire::test()`, browser tests |
| [nesting.md](nesting.md) | Nesting | Props, slots, `#[Reactive]`, `#[Modelable]`, events, `$parent` |
| [alpine.md](alpine.md) | Alpine | `$wire` object, calling methods, `@js` directive |
| [javascript.md](javascript.md) | JavaScript | Component scripts, interceptors, `$wire` ref, global Livewire |
| [pages.md](pages.md) | Pages | `Route::livewire()`, layouts, titles, route model binding |

## Critical v3 → v4 Differences

1. **`wire:model` on `<select>` defers updates** — use `wire:model.live` for immediate sync
2. **`mount()` is only called on initial render**, NOT on every Livewire request
3. The **`updated*` hook** fires automatically when `wire:model.live` changes a property
4. For **dependent selects**, always add `wire:key` to force proper re-rendering
5. **`$event`** is available in `wire:change`, `wire:keydown`, etc. for accessing the native event
6. **Default component format** is single-file (⚡emoji), not class-based
