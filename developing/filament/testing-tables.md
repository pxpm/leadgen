# Testing Tables (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/testing/testing-tables
`Livewire::test(ListUsers::class)`
### Assertions: `->assertCanSeeTableRecords($users)` `->assertCanNotSeeTableRecords($hidden)` `->assertTableColumnExists("email")` `->assertTableColumnVisible("email")` `->assertTableColumnHidden("secret")` `->assertTableColumnStateSet("name","John")` `->assertTableColumnFormattedStateSet("created_at","2024-01-01")`
### Actions: `->sortTable("name")` `->sortTable("name","desc")` `->searchTable("john")` `->removeTableSearch()` `->filterTable("is_active")` `->removeTableFilter("is_active")` `->callTableAction("edit",$record)` `->callTableColumnAction("email",$record)` `->callMountedTableAction()`
### Bulk: `->selectTableRecords([$r1->id,$r2->id])` `->deselectTableRecords([$r1->id])` `->callTableBulkAction("delete")`
### Pagination: `->assertSet("tableRecordsPerPage",10)` `->call("gotoPage",3)`
