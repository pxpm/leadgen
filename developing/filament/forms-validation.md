# Forms Validation (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/forms/validation
### Rules as Methods
`->required()` `->maxLength(255)` `->minLength(3)` `->email()` `->numeric()` `->unique('users','email',ignorable:$record)` `->exists('users','id')` `->confirmed()` `->in(['a','b'])` `->notIn(['x'])` `->regex('/^[a-z]+$/')`
### Custom: `->rule('phone')` `->rules([new MyCustomRule])`
### Conditional: `->required(fn($get)=>$get('type')==='company')` `->requiredIf('type','company')`
### Messages: `->validationMessages(['required'=>'Need this'])` `->validationAttribute('Email')`
### Disable save validation: `->saved(false)->validated(true)` validate but dont save.
