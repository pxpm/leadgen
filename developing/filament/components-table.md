# Rendering Table in Blade (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/components/table
```php
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Livewire\Component;

class MyTable extends Component implements HasTable {
    use InteractsWithTable;
    public function table(Table $table): Table {
        return $table->query(User::query())->columns([TextColumn::make("name")]);
    }
}
```
Blade: `<div>{{ $this->table }}</div>`
