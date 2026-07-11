# Replicate Action (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/actions/replicate
`ReplicateAction::make()` duplicates record to Create page. `->excludeAttributes(["slug"])` `->beforeReplicaSaved(fn($r,$d)=>$r->title=$d["title"]." (copy)")`
