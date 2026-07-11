# Actions (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/actions/overview

## Basic
`Action::make('delete')->requiresConfirmation()->action(fn()=>$this->post->delete())`

## With Form
`Action::make('send')->schema([TextInput::make('subject')])->action(fn(array $data)=>...)`

## URL
`Action::make('edit')->url(fn()=>route('posts.edit',['post'=>$this->post]))`

## Trigger Styles
`->button()` `->link()` `->iconButton()` `->badge()` `->outlined()` `->labeledFrom('md')`

## Styling
`->label('X')` `->color('danger')` `->size(Size::Large)` `->icon('heroicon-m-pencil')` `->iconPosition(IconPosition::After)` `->badge(5)->badgeColor('success')`

## Auth
`->visible(fn()=>auth()->user()->can('update'))` `->authorize('update')` `->authorizationTooltip()` `->disabled()`

## Built-in
CreateAction EditAction ViewAction DeleteAction ReplicateAction ForceDeleteAction RestoreAction ImportAction ExportAction

## Schema Actions
`TextInput::make('t')->afterContent(Action::make('slug')->action(fn(Get $g,Set $s)=>$s('slug',str($g('t'))->slug())))` `->actionJs("...")`

## Other
`->keyBindings(['command+s'])` `->rateLimit(5)` `->extraAttributes([...])`

## Utility: $action $data $record $arguments $livewire $schemaGet $schemaSet
