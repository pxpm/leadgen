# Widgets (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/widgets/overview
### Chart Widgets
`ChartWidget::make()->heading('Users')->chart([...])->type('line')`
### Stats Overview
`StatsOverviewWidget::make()->cards([Stat::make('Users',$count)->icon('heroicon-o-user')])`
### Custom Widgets
Extend `Widget` and define `protected string $view`. Use `$columnSpan`.
### Registration
In panel: `->widgets([...])` `->discoverWidgets(in:...)`
