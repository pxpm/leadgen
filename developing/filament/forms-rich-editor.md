# Forms Rich Editor (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/forms/rich-editor
`RichEditor::make('content')->toolbarButtons(['bold','italic','heading','link','blockquote','codeBlock'])` `->disableToolbarButtons(['codeBlock'])` `->fileAttachmentsDirectory('attachments')` `->fileAttachmentsDisk('public')` `->fileAttachmentsVisibility('public')`
### Security: File attachment IDs (`data-id`) can be tampered. Use `->authorizeExistingFileAttachmentPaths()`.
