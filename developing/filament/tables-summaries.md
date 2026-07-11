# Table Summaries (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/tables/summaries
### Column Summaries
```php
TextColumn::make('price')->summarize([
    Summarizers\Sum::make()->label('Total'),
    Summarizers\Average::make()->label('Avg'),
    Summarizers\Count::make()->label('N'),
    Summarizers\Range::make()->label('Range'),
])
```
### Available: Sum Average Count Min Max Range Trend Values
### Custom:
`Summarizers\Summarizer::make()->using(fn(Collection $r)=>$r->median('price'))`
### Formatting: `Summarizers\Sum::make()->numeric(precision:2)`
