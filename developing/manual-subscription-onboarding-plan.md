# Manual Subscription & Onboarding — Implementation Plan

> **Status**: Approved — ready for implementation
> **Date**: 2026-07-10
> **Context**: Stripe integration is deferred. All subscriptions are managed manually by super-admin via Filament. This plan covers the data model, service layer, plan system, usage tracking, and Filament UI.

---

## 1. Architecture Overview

```
┌──────────┐     ┌──────────────┐     ┌───────────────┐
│  Tenant  │────→│ Subscription │←────│     Plan      │
│          │     │  plan_id     │     │  name, slug   │
│          │     │  status      │     │  limits (JSON)│
│          │     │  trial/ends  │     │  is_public    │
└──────────┘     └──────────────┘     └───────────────┘
                                           │
                                           │ limits define
                                           ▼
                                  ┌─────────────────┐
                                  │   usage_logs    │
                                  │  tenant_id      │
                                  │  type (sms/     │
                                  │   email/ai)     │
                                  │  count (monthly)│
                                  └─────────────────┘
```

- **Plans** are the atomic unit. They have limits (SMS, email, AI ingestion) and a `is_public` flag.
- **Public plans** appear on the pricing page for self-serve signup (future frontend).
- **Non-public plans** are custom, created by super-admin, assignable to specific tenants.
- **No unlimited plans.** Every plan has explicit numeric limits.
- **Usage is tracked** in a dedicated `usage_logs` table — not inferred from notifications.
- **`SubscriptionTier` enum is dropped.** Plans replace it entirely.

---

## 2. Data Model Changes

### 2.1 New: `plans` table

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| name | varchar(255) | e.g. "Starter", "Professional", "Enterprise" |
| slug | varchar(100) | Unique, e.g. "starter", "professional", "enterprise" |
| description | text | nullable — human-readable description |
| limits | json | `{"sms_monthly": 100, "email_monthly": 500, "ai_ingestion_monthly": 50}` |
| is_public | boolean | Public plans shown on pricing page; non-public are super-admin-only assignment |
| sort_order | smallint unsigned | Display order on pricing page |
| is_active | boolean | Can be toggled off to retire a plan |
| created_at | timestamp | |
| updated_at | timestamp | |

### 2.2 New: `usage_logs` table

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| tenant_id | bigint unsigned (FK → tenants) | |
| type | varchar(20) | `sms`, `email`, `ai_ingestion` |
| count | int unsigned | Incremented per usage event |
| period | varchar(7) | Month in `YYYY-MM` format |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique index on `(tenant_id, type, period)` — upsert pattern: increment count for current month.

### 2.3 Modify: `subscriptions` table

Drop the `tier` column. Add `plan_id` FK:

```php
// REMOVE:
$table->string('tier', 50)->default('standard');

// ADD:
$table->foreignId('plan_id')->constrained()->cascadeOnDelete();
```

Also make Stripe fields nullable (manual subs have no Stripe IDs):

```php
$table->string('stripe_subscription_id')->nullable();
$table->string('stripe_price_id')->nullable();
```

### 2.4 Drop `subscription_tier` from `tenants`

Remove the column from the migration. Replace with an accessor on the Tenant model:

```php
// Tenant model
public function activeSubscription(): HasOne
{
    return $this->hasOne(Subscription::class)->whereIn('status', ['active', 'trialing']);
}

public function getPlanAttribute(): ?Plan
{
    return $this->activeSubscription?->plan;
}
```

### 2.5 Keep `stripe_customer_id` on `tenants`

Tenant-level, already nullable. Stays.

### 2.6 Drop `SubscriptionTier` enum

Replaced by the `Plan` model. No more hardcoded tier cases.

---

## 3. Models

### 3.1 `Plan` model

```php
class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'limits',
        'is_public', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'limits' => 'array',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)->where('is_active', true);
    }

    public function getLimit(string $type): int
    {
        return $this->limits[$type] ?? 0;
    }
}
```

### 3.2 `UsageLog` model

```php
class UsageLog extends Model
{
    protected $fillable = ['tenant_id', 'type', 'count', 'period'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function incrementUsage(Tenant $tenant, string $type): void
    {
        $period = now()->format('Y-m');
        self::upsert(
            ['tenant_id' => $tenant->id, 'type' => $type, 'period' => $period, 'count' => 1],
            ['tenant_id', 'type', 'period'],
            ['count' => DB::raw('count + 1')]
        );
    }

    public static function getUsage(Tenant $tenant, string $type): int
    {
        return (int) self::where('tenant_id', $tenant->id)
            ->where('type', $type)
            ->where('period', now()->format('Y-m'))
            ->value('count') ?? 0;
    }
}
```

### 3.3 Update `Subscription` model

```php
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'plan_id', 'stripe_subscription_id', 'stripe_price_id',
        'status', 'trial_ends_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'trial_ends_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo { ... }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
}
```

### 3.4 Update `Tenant` model

```php
// Add:
public function activeSubscription(): HasOne
{
    return $this->hasOne(Subscription::class)->whereIn('status', ['active', 'trialing']);
}

public function getPlanAttribute(): ?Plan
{
    return $this->activeSubscription?->plan;
}

public function isActive(): bool
{
    return $this->subscriptions()
        ->whereIn('status', ['active', 'trialing'])
        ->exists();
}

// Remove subscription_tier from $fillable and casts()
// Remove getSubscriptionTierAttribute() if present
```

---

## 4. Services

### 4.1 `TenantService` (`app/Services/TenantService.php`)

**`createTenant(array $data): Tenant`**

Input:
```php
[
    'name'                   => 'Company Name',
    'slug'                   => 'company-slug',
    'locale'                 => 'pt',
    'industry_id'            => 1,
    'admin_name'             => 'Admin User',
    'admin_email'            => 'admin@company.pt',
    'admin_password'         => null,            // auto-generated
    'plan_id'                => 1,               // required — which plan to assign
    'subscription_status'    => 'active',
    'trial_ends_at'          => null,
    'stripe_customer_id'     => null,
    'stripe_subscription_id' => null,
    'stripe_price_id'        => null,
    'branding_config'        => [],
    'notification_config'    => [],
    'active_services'        => [],
    'service_config'         => [],
    'qualification_overrides'=> [],
    'send_magic_link'        => true,
]
```

Logic (DB::transaction):
1. Validate slug uniqueness
2. Create Tenant
3. Auto-generate password if null
4. Create User (admin, `is_super_admin = false`)
5. Create Subscription (with `plan_id`)
6. If `send_magic_link`, dispatch magic link job
7. Return Tenant

**`isServiceActive(Tenant $tenant): bool`** — has active/trialing subscription?

**`sendMagicLinkForFirstLogin(User $user): void`** — dispatch email with magic link

**`updateSubscription(Tenant $tenant, array $data): Subscription`** — change plan, status, dates

### 4.2 `PlanLimitService` (`app/Services/PlanLimitService.php`)

Dedicated service for checking and enforcing plan limits:

- `canSendSms(Tenant $tenant): bool`
- `canSendEmail(Tenant $tenant): bool`
- `canIngestAi(Tenant $tenant): bool`
- `getUsage(Tenant $tenant, string $type): int` — queries `usage_logs`
- `getLimit(Tenant $tenant, string $type): int` — from tenant's active plan limits
- `recordUsage(Tenant $tenant, string $type): void` — increments `usage_logs`

All methods throw if tenant has no active subscription.

---

## 5. Filament Resources

### 5.1 `PlanResource` (super-admin only)

**File**: `app/Filament/Resources/PlanResource.php`

**Table columns**: Name, Slug, SMS Limit, Email Limit, AI Ingestion Limit, Public (boolean icon), Active (boolean icon), Sort Order

**Form**:
- Name (text, required)
- Slug (text, required, unique)
- Description (textarea)
- Limits section:
  - SMS Monthly (integer, required, min: 0)
  - Email Monthly (integer, required, min: 0)
  - AI Ingestion Monthly (integer, required, min: 0)
- is_public (toggle)
- is_active (toggle)
- sort_order (integer, default: 0)

**Access**: Only super-admin. Visible in navigation only to super-admin.

### 5.2 `SubscriptionsRelationManager` (on TenantResource)

**File**: `app/Filament/Resources/TenantResource/RelationManagers/SubscriptionsRelationManager.php`

**Table columns**: ID, Plan Name (badge), Status (color badge), Trial Ends, Ends At, Created At

**Create form**:
- Plan (select from all active plans — public AND non-public)
- Status (select)
- Trial Ends At (date picker, nullable)
- Ends At (date picker, nullable)

**Edit**: Change plan, status, dates. If activating a new subscription, prompt to mark previous active one as canceled.

**Status badge colors**: active→green, trialing→blue, past_due→amber, canceled→gray

### 5.3 Update `TenantResource`

**Form changes (EditTenant)**:
- Remove `subscription_tier` Select
- Add disabled/read-only field showing current plan (from relationship)

**Table changes**:
- Replace `subscription_tier` TextColumn with one reading `subscriptions.plan.name`

**ViewTenant infolist**:
- Replace `subscription_tier` with plan name from relationship

**Add relation manager**:
```php
public static function getRelations(): array
{
    return [
        RelationManagers\SubscriptionsRelationManager::class,
    ];
}
```

**Add `CreateTenant` page** (see §6).

---

## 6. Filament — Create Tenant Page

**File**: `app/Filament/Resources/TenantResource/Pages/CreateTenant.php`

### 6.1 Form sections

**Section 1 — Company Info**:
- Name (text, required)
- Slug (text, required, auto-generated via JS)
- Locale (select: pt, en; default pt)
- Industry (select, relationship, required)

**Section 2 — Admin User**:
- Admin Name (text, required)
- Admin Email (email, required)
- Send Magic Link (toggle, default: on — emails admin a magic link for first login)

**Section 3 — Subscription**:
- Plan (select from ALL active plans — public and non-public. Required.)
- Status (select, default: active)
- Trial Ends At (date picker, nullable)

### 6.2 After creation
- Success notification: "Tenant [Name] created. Magic link sent to [admin email]."
- Redirect to tenant view page

### 6.3 Access control
Super-admin only. Add `CreateTenant` to `ListTenants::getHeaderActions()`.

---

## 7. Middleware — Service Access Gate

**File**: `app/Http/Middleware/EnsureActiveSubscription.php`

- Super-admins: always pass through
- Tenant users: check `TenantService::isServiceActive($user->tenant)`
- Widget API: return 402 JSON if inactive
- Filament (tenant users): redirect to "Subscription Inactive" page

---

## 8. Seeders

### 8.1 `PlanSeeder`

Creates 3 default public plans:

```php
Plan::create([
    'name' => 'Starter',
    'slug' => 'starter',
    'description' => 'For small contractors getting started.',
    'limits' => ['sms_monthly' => 100, 'email_monthly' => 500, 'ai_ingestion_monthly' => 50],
    'is_public' => true,
    'sort_order' => 1,
    'is_active' => true,
]);

Plan::create([
    'name' => 'Professional',
    'slug' => 'professional',
    'description' => 'For growing businesses with more volume.',
    'limits' => ['sms_monthly' => 500, 'email_monthly' => 2000, 'ai_ingestion_monthly' => 200],
    'is_public' => true,
    'sort_order' => 2,
    'is_active' => true,
]);

Plan::create([
    'name' => 'Enterprise',
    'slug' => 'enterprise',
    'description' => 'For high-volume operations with custom needs.',
    'limits' => ['sms_monthly' => 5000, 'email_monthly' => 10000, 'ai_ingestion_monthly' => 1000],
    'is_public' => true,
    'sort_order' => 3,
    'is_active' => true,
]);
```

Super admin can later add non-public custom plans via `PlanResource`.

### 8.2 Update `DatabaseSeeder`

Use `TenantService::createTenant()` with `plan_id` for the demo tenant.

---

## 9. Tests

### 9.1 `PlanFactory`
States: `public()`, `private()`, `active()`, `inactive()`.

### 9.2 `SubscriptionFactory`
States: `active()`, `trialing()`, `canceled()`, `pastDue()`. Belongs to a `Plan` and `Tenant`.

### 9.3 `TenantServiceTest`
- `createTenant` creates tenant + user + subscription
- Slug uniqueness violation rolls back
- `send_magic_link = true` dispatches job
- `isServiceActive` correct for each status
- `plan_id` is required

### 9.4 `PlanLimitServiceTest`
- `canSendSms` returns false when usage ≥ limit
- `canSendSms` returns true when under limit
- `recordUsage` upserts correctly
- `getUsage` returns correct count for current month
- Usage from previous month doesn't affect current
- Throws if no active subscription

### 9.5 `SubscriptionsRelationManagerTest`
- List subscriptions for tenant
- Create manual subscription (public + non-public plans available)
- Edit subscription plan/status
- Delete subscription

### 9.6 `CreateTenantTest`
- Super-admin access, non-super-admin blocked
- Form validation
- All plans (public + non-public) appear in plan select
- Successful creation + redirect

### 9.7 `EnsureActiveSubscriptionTest`
- Super-admin bypasses
- Active/trialing allowed
- Canceled/past_due blocked
- Widget API returns 402

### 9.8 `PlanResourceTest`
- Super-admin can CRUD plans
- Non-super-admin cannot access
- `is_public` flag works correctly

---

## 10. Execution Order

1. **Create `Plan` migration** — `php artisan make:migration create_plans_table`
2. **Create `usage_logs` migration** — `php artisan make:migration create_usage_logs_table`
3. **Modify `subscriptions` migration** — drop `tier`, add `plan_id` FK, make Stripe fields nullable
4. **Modify `tenants` migration** — drop `subscription_tier`
5. **Create `Plan` model** + `PlanFactory`
6. **Create `UsageLog` model**
7. **Update `Subscription` model** — add `plan()` relationship, remove `tier` cast
8. **Update `Tenant` model** — add `activeSubscription()`, `plan` accessor, `isActive()`, remove `subscription_tier`
9. **Delete `SubscriptionTier` enum**
10. **Create `PlanLimitService`** + test
11. **Create `TenantService`** + test
12. **Create `PlanResource`** + test
13. **Create `SubscriptionsRelationManager`** + test
14. **Create `CreateTenant` page** + test
15. **Create `EnsureActiveSubscription` middleware** + test
16. **Update `TenantResource`** — add relation manager, update form/table/infolist, add create page
17. **Create `PlanSeeder`** + update `DatabaseSeeder`
18. **Refactor `StripeWebhookController`** to use `TenantService`
19. **Run `migrate:fresh --seed`**
20. **Run `php artisan test --compact`**
21. **Run `vendor/bin/pint --dirty --format agent`**

---

## 11. Decisions Log

| # | Decision | Detail |
|---|----------|--------|
| 1 | Plans replace tiers | `SubscriptionTier` enum deleted. `Plan` model with DB-driven limits. |
| 2 | `is_public` flag on plans | Public = self-serve (future frontend). Non-public = super-admin assignment only. |
| 3 | All plans have explicit limits | No "unlimited" plans. Every limit is a non-negative integer. |
| 4 | Dedicated `usage_logs` table | Usage tracked independently. Upsert pattern per tenant/type/month. |
| 5 | Drop `subscription_tier` from tenants | Single source of truth = `subscriptions.plan_id`. |
| 6 | Stripe fields nullable | Manual subscriptions have null Stripe IDs. |
| 7 | Admin password auto-generated | Never shown. Magic link email sent for first login. |
| 8 | Canceled = no services, tenant stays | Can log in, see status, reactivate. Middleware gates access. |
| 9 | Everything tested at build time | Factory + feature tests for every component. |
| 10 | Industry selected at tenant creation | Currently `construcao_civil` only. |
| 11 | Super-admin manages plans via `PlanResource` | Full CRUD for plans, including non-public custom plans. |
