# Testing Actions (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/testing/testing-actions
### Table Actions: `->callTableAction("edit",$record)` `->assertTableActionVisible("edit")` `->assertTableActionHidden("delete")` `->assertTableActionEnabled("edit")` `->assertTableActionDisabled("delete")`
### Mounted Actions: `->mountTableAction("edit",$record)` `->assertMountedTableActionFormSet(["name"=>"John"])` `->setMountedTableActionFormData(["name"=>"Jane"])` `->callMountedTableAction()` `->assertMountedTableActionNotified("Saved")` `->unmountTableAction()`
### Bulk Actions: `->selectTableRecords([$r1->id])` `->callTableBulkAction("delete")` `->mountTableBulkAction("export")` `->callMountedTableBulkAction()`
### Page Actions: `->callAction("create")` `->mountAction("delete",["record"=>$r->id])` `->callMountedAction()`
