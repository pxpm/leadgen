# Rendering Form in Blade (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/components/form
```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class MyForm extends Component implements HasForms {
    use InteractsWithForms;
    public array $data = [];
    public function mount(): void { $this->form->fill(); }
    public function form(Form $form): Form {
        return $form->schema([TextInput::make("name")->required()])->statePath("data");
    }
    public function submit(): void { $data = $this->form->getState(); /* save */ }
}
```
Blade: `<form wire:submit="submit">{{ $this->form }}</form>`
Or: `<x-filament-panels::form wire:submit="submit">{{ $this->form }}</x-filament-panels::form>`
