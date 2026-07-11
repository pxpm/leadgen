# Render Hooks (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/advanced/render-hooks
### Panel Hooks
`->renderHook(PanelsRenderHook::BODY_START,fn()=>Blade::render("@livewire('my-comp')"))`
### Available Hooks
`HEAD_START HEAD_END BODY_START BODY_END CONTENT_START CONTENT_END FOOTER SIDEBAR_NAV_START SIDEBAR_NAV_END TOPBAR_START TOPBAR_END`
### Global Hooks
`FilamentView::registerRenderHook(PanelsRenderHook::BODY_END,fn()=>'...')` in service provider.
