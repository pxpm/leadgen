# Forms File Upload (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/forms/file-upload
`FileUpload::make('avatar')->image()->directory('avatars')->disk('public')` `->multiple()` `->acceptedFileTypes(['image/jpeg','image/png'])` `->maxSize(5120)` `->imageResizeMode('cover')` `->imageEditor()` `->openable()` `->downloadable()` `->deletable()` `->reorderable()` `->preserveFilenames()` `->visibility('public')` `->storeFileNamesIn('attachment_file_names')`
### Security: Always validate file types. Use `->authorizeExistingFilePaths()` fn. File names from users can be dangerous - consider `->preserveFilenames(false)`.
