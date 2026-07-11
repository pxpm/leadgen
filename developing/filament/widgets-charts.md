# Chart Widgets (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/widgets/charts
Extend `ChartWidget`: `protected function getType(): string { return "line"; }` types: `line bar pie doughnut`
`protected function getData(): array { return ["datasets"=>[["label"=>"Users","data"=>[1,5,10]]],"labels"=>["Jan","Feb","Mar"]]; }`
### Options: `->heading("Chart")` `->description("Desc")` `->colors(["primary"])` `->columnSpan(2)` `->maxHeight("300px")`
### Filters: `protected function getFilters(): ?array { return ["today"=>"Today","week"=>"Week"]; }` then filter data in getData()
