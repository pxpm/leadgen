# Testing Schemas (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/testing/testing-schemas
`Livewire::test(EditUser::class,["record"=>$user->id])`
### Assertions: `->assertFormFieldExists("name")` `->assertFormFieldIsVisible("email")` `->assertFormFieldIsHidden("secret")` `->assertFormFieldIsDisabled("email")` `->assertFormFieldIsEnabled("name")`
### State: `->assertFormSet(["name"=>"John"])` `->fillForm(["name"=>"Jane","email"=>"j@e.com"])` `->assertFormFieldState("name","John")`
### Validation: `->fillForm(["email"=>"bad"])->call("save")->assertHasFormFieldErrors(["email"=>"email"])` `->assertHasNoFormFieldErrors()`
### Schema components: `TableColumn::make("name")` can be tested with `assertTableColumnExists()`
