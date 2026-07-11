# Testing Resources (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/testing/testing-resources
`Livewire::test(ListUsers::class)->assertCanSeeTableRecords($users)`
### Table assertions: `->assertCanRenderTableColumn("email")` `->assertTableColumnStateSet("name","John")` `->sortTable("name")` `->searchTable("john")`
### Form assertions: `->fillForm(["name"=>"John"])` `->call("save")` `->assertFormSet(["name"=>"John"])` `->assertFormFieldExists("name")`
### Authorization: `->assertStatus(403)` for unauthorized
### Filters: `->filterTable("is_active")` `->removeTableFilter("is_active")` `->assertDontSeeTableRecords($hidden)`
### Actions: `->callTableAction("delete",$record)` `->callMountedTableAction()`
### Notifications: `->assertNotified()` `->assertNotified("Saved")`
### Create: `Livewire::test(CreateUser::class)->fillForm([...])->call("create")`
### Edit: `Livewire::test(EditUser::class,["record"=>$user->id])->fillForm([...])->call("save")`
