# Stats Overview Widgets (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/widgets/stats-overview
`StatsOverviewWidget::make()->cards([Stat::make("Users",User::count())->icon("heroicon-o-user")->description("3% increase")->descriptionIcon("heroicon-m-arrow-trending-up")->color("success")->url(route("users.index"))])`
### Chart in stat: `->chart([7,2,10,3,15,4,17])` `->chartColor("success")`
### Extra attributes: `->extraAttributes(["class"=>"cursor-pointer"])`
