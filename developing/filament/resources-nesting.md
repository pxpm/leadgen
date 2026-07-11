# Nested Resources (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/resources/nesting
Child resource: `protected static ?string $parentResource = PostResource::class;`
URLs: `/admin/posts/{post}/comments` parent auto-resolved
`getPages()` uses `{post}` parameter. Auth checks both parent and child policies.
Custom binding: `getParentResourceRouteBinding()`
