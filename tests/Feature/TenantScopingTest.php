<?php

use App\Models\Industry;
use App\Models\Lead;
use App\Models\Tenant;

beforeEach(function () {
    $industry = Industry::factory()->create();
    $this->tenantA = Tenant::factory()->create(['industry_id' => $industry->id]);
    $this->tenantB = Tenant::factory()->create(['industry_id' => $industry->id]);
});

test('lead query is tenant scoped via BelongsToTenant trait', function () {
    $leadA = Lead::factory()->create(['tenant_id' => $this->tenantA->id]);
    Lead::factory()->create(['tenant_id' => $this->tenantB->id]);

    // Without tenant context, no scope applied
    expect(Lead::count())->toBe(2);

    // With tenant context, only that tenant's leads visible
    app()->instance('current_tenant', $this->tenantA);
    expect(Lead::count())->toBe(1);
    expect(Lead::first()->id)->toBe($leadA->id);
});
