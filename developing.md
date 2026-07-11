# Lead Intake Assistant — Development Blueprint

> **Source of truth** synthesizing all 4 spec documents in `developing/`.
> No code shall be written until this document is 100% complete and approved.
> If this contradicts a spec document, this wins — but flag the discrepancy.

---

## 1. What We're Building

A **multi-tenant SaaS lead qualification platform** for trade businesses. Starting niche: **roofing contractors**.

A homeowner visits a contractor's website → opens a chat widget → has an AI-driven conversation that qualifies their need → the contractor receives a structured, scored lead with photos and summary.

**We STOP at lead qualification and delivery.** We do NOT build: CRM, pipelines, scheduling, calendars, invoicing, estimates, payments, job management, or customer portals.

---

## 2. Product Principles

1. Solve qualification only — nothing else.
2. AI drives the conversation; the platform owns the business rules.
3. Structured data is always the source of truth, not raw conversation text.
4. Every record belongs to a tenant. Every query is tenant-scoped.
5. New industries must be configurable, not require code changes.
6. Optimize for fast onboarding — a customer should go from signup to receiving leads within the same day.
7. Mobile-first. Homeowners use phones.
8. All expensive operations (AI, images, summaries, notifications) are queued.
9. No enum types in the database — enums live in PHP code, database stores strings.
10. Configuration must be flexible enough to support tenant-specific business logic (e.g., site visit fees, custom qualification flows).
11. Locale-aware from day one — built first in Portuguese (pt), structured so adding languages requires only config additions, not code changes.

---

## 3. Technology Stack (Decided)

| Layer | Choice | Rationale |
|-------|--------|-----------|
| Backend | Laravel 13, PHP 8.5 | — |
| Admin Panel | Filament 5 + Livewire 4 | Admin only; never use Filament for customer-facing UI |
| AI SDK | `laravel/ai` (v0) | Provider-agnostic abstraction |
| AI Model (MVP) | DeepSeek | Lowest cost; provider swappable via `laravel/ai` |
| Frontend Widget | Vanilla JS Web Component | Zero framework overhead; bundled by Vite into single `.js` file with inlined CSS; ~5KB gzipped max |
| Main Database | MySQL | — |
| Queue Database | SQLite | Separate lightweight DB for queue jobs to avoid overloading main DB |
| File Storage | Local disk (MVP) with Spatie MediaLibrary | Uses Laravel filesystem disks; easy migration to S3/R2 later |
| SMS | Twilio (swappable) | Via `App\Contracts\SmsProvider` interface |
| Email | bentonow.com (swappable) | Via Laravel's mail transport system |
| Billing | Stripe | Paid tiers only; no free tier |
| CSS | Tailwind CSS v4 | — |
| Testing | Pest 4 | Feature + Unit + Browser; comprehensive coverage |

---

## 4. Decisions Log

| # | Decision | Detail |
|---|----------|--------|
| 1 | Widget: Vanilla JS Web Component | No framework. Vite-bundled single file. Mobile-first. |
| 2 | AI: DeepSeek | Cheapest. `laravel/ai` SDK for future swap. |
| 3 | Missed calls: Per-tenant Twilio landline numbers (default), shared number fallback | Each tenant gets a dedicated +351 landline number via Twilio API or manual provisioning. Webhook identifies tenant directly via `To` field — no `ForwardedFrom` dependency. Shared number with `ForwardedFrom` matching as fallback for tenants who don't want a dedicated number. |
| 4 | SMS: Twilio with `SmsProvider` contract | Swappable. |
| 5 | Email: bentonow.com with mail transport | Swappable via standard Laravel mail driver. |
| 6 | Multi-tenancy: Single DB with `tenant_id` | Global scopes on every model. |
| 7 | Widget hosting: Same-domain sub-path | Served from Laravel app (e.g. `/js/widget.js`). No CORS issues. |
| 8 | Lead auth: Signed URLs | Per-lead unique signed URL for conversation resumption. No passwords. |
| 9 | Testing: Comprehensive | Every service, flow, and edge case covered. |
| 10 | Queue: SQLite database | Separate DB for queues to avoid overloading MySQL main DB. |
| 11 | File storage: Local + Spatie MediaLibrary | Uses Laravel disks. Easy migration to S3/R2 later. |
| 12 | No enums in DB | All "enum-like" columns store strings. PHP enums in `app/Enums/` handle validation/mapping. |
| 13 | Subscriptions: Single paid plan (MVP) | One plan, one price, all features. Stripe handles billing. Architecture supports multiple plans later (differentiated by SMS volume, not features). |
| 14 | SMS magic links for contractors | Contractor SMS notifications include a magic link that auto-authenticates them to view the lead. |
| 15 | API security: Rate limiting + throttling | Public widget endpoints are rate-limited per IP + per tenant slug. Webhook endpoints validated via Twilio signature. |
| 16 | Portuguese-first, locale-aware config | All configs stored with `locales.{locale}` wrapper. MVP: `pt` only. Adding languages = adding locale blocks to JSON configs. No code changes. |

---

## 5. Database Schema (Complete)

> **Rule**: No `ENUM` columns. All enum-like values are stored as `varchar` and mapped to PHP enums in `app/Enums/`.
> **MySQL**: Use `json` (not `jsonb`). Use `InnoDB`. All FKs are `bigint unsigned`.

### 5.1 `tenants`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| name | varchar(255) | Company name |
| slug | varchar(255) | Unique URL-safe identifier |
| locale | varchar(5) | Locale for widget + AI conversation. Defaults to industry default_locale. e.g. "pt", "en" |
| industry_id | bigint unsigned (FK → industries) | |
| subscription_tier | varchar(50) | PHP enum `SubscriptionTier`. MVP: single "standard" tier. Future: tier determines SMS volume limits. |
| stripe_customer_id | varchar(255) | nullable |
| twilio_phone_number | varchar(50) | Dedicated Twilio landline number for this tenant (nullable if using shared number). e.g. "+351210000001" |
| twilio_phone_sid | varchar(255) | Twilio Phone Number SID (nullable). Used for management via API. |
| branding_config | json | Colors (see §8.2) |
| notification_config | json | Email/SMS recipients, enabled flags, missed call SMS template (see §8.3) |
| qualification_overrides | json | Customer-specific field overrides (nullable) (see §8.4) |
| created_at | timestamp | |
| updated_at | timestamp | |

> **Logo**: The Tenant model uses Spatie MediaLibrary with a `logo` collection. No dedicated `logo` column — MediaLibrary's `media` table handles the file path and metadata.

### 5.2 `tenant_phone_numbers`

> Tenants can have multiple business phone numbers. All are used for missed call matching.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| tenant_id | bigint unsigned (FK → tenants) | |
| phone_number | varchar(50) | E.164 format |
| is_primary | boolean | Primary number for display |
| created_at | timestamp | |

Unique index on `(tenant_id, phone_number)`.

### 5.3 `tenant_excluded_numbers`

> Phone numbers the tenant wants to exclude from missed call recovery (personal contacts, family, known spam).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| tenant_id | bigint unsigned (FK → tenants) | |
| phone_number | varchar(50) | Number to exclude from recovery |
| label | varchar(255) | Optional: "Mom", "Supplier X" |
| created_at | timestamp | |

Unique index on `(tenant_id, phone_number)`.

### 5.4 `users` (admin users — Filament login)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| tenant_id | bigint unsigned (FK → tenants) | Every admin user belongs to a tenant |
| name | varchar(255) | |
| email | varchar(255) | Unique per tenant for login |
| password | varchar(255) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 5.5 `industries`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| slug | varchar(100) | e.g. "roofing", "hvac" |
| name | varchar(255) | e.g. "Roofing Contractors" |
| config | json | Full industry definition (see §8.1) |
| is_active | boolean | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 5.6 `leads`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| tenant_id | bigint unsigned (FK → tenants) | |
| industry_id | bigint unsigned (FK → industries) | |
| status | varchar(50) | PHP enum `LeadStatus`: new, in_progress, qualified, delivered |
| source | varchar(50) | PHP enum `LeadSource`: widget, missed_call, direct_link |
| qualification_score | smallint unsigned | 1-10, nullable until qualified |
| session_token | varchar(128) | Unique token for conversation resumption |
| conversation_started_at | timestamp | |
| qualified_at | timestamp | |
| delivered_at | timestamp | |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique index on `session_token`.

### 5.7 `lead_fields` (structured extracted data)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| lead_id | bigint unsigned (FK → leads) | |
| field_key | varchar(100) | e.g. "contact_name", "roof_type" |
| field_type | varchar(50) | PHP enum `FieldType`: text, select, boolean, number. Controls widget rendering (chips for select, keyboard for text). |
| field_value | text | The extracted/normalized value |
| field_options | json | For `select` type: array of options the user can choose from (e.g. ["tile", "slate", "metal", "asbestos", "other"]) |
| confidence | decimal(3,2) | 0.00–1.00 extraction confidence |
| is_required | boolean | Was this a required field at time of collection? |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique index on `(lead_id, field_key)`.

### 5.8 `lead_media` (via Spatie MediaLibrary)

Spatie MediaLibrary handles the `media` table automatically. Media is associated with the `Lead` model. Stored on the local disk configured as `media` disk in `config/filesystems.php` (pointing to `storage/app/media`). When migrating to S3/R2, only the disk config changes.

Media collections on Lead model:
- `photos` — images uploaded during conversation
- `documents` — PDFs (future MVP)

### 5.9 `conversation_messages`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| lead_id | bigint unsigned (FK → leads) | |
| role | varchar(20) | PHP enum `MessageRole`: user, assistant, system |
| content | text | Message body |
| metadata | json | Extracted fields, tool calls, confidence, model used |
| created_at | timestamp | |

### 5.10 `notifications`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| tenant_id | bigint unsigned (FK → tenants) | |
| lead_id | bigint unsigned (FK → leads) | |
| channel | varchar(20) | PHP enum `NotificationChannel`: email, sms |
| recipient | varchar(255) | Email address or phone number |
| status | varchar(20) | PHP enum `NotificationStatus`: pending, sent, failed |
| sent_at | timestamp | |
| error_message | text | nullable |
| created_at | timestamp | |

### 5.11 `missed_calls`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| tenant_id | bigint unsigned (FK → tenants) | Matched via tenant's dedicated Twilio number (`To` field) or via `ForwardedFrom` |
| caller_number | varchar(50) | The homeowner's phone number |
| tenant_phone | varchar(50) | The Twilio number that received the call (tenant's dedicated number or platform shared number) |
| twilio_call_sid | varchar(255) | Twilio Call SID (used for idempotency) |
| matched_by | varchar(20) | How tenant was identified: `dedicated_number` or `forwarded_from` |
| sms_sent | boolean | |
| lead_id | bigint unsigned (FK → leads) | nullable — created after SMS click |
| created_at | timestamp | |

### 5.12 `magic_links` (contractor auth for SMS links)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| user_id | bigint unsigned (FK → users) | Which contractor user this authenticates |
| token | varchar(128) | Unique magic link token |
| redirect_to | varchar(500) | URL to redirect after auth (e.g. lead detail page) |
| used_at | timestamp | nullable |
| expires_at | timestamp | |
| created_at | timestamp | |

Unique index on `token`.

### 5.13 `subscriptions`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned (PK) | |
| tenant_id | bigint unsigned (FK → tenants) | |
| stripe_subscription_id | varchar(255) | |
| stripe_price_id | varchar(255) | |
| tier | varchar(50) | PHP enum `SubscriptionTier`. MVP: "standard". Future: "starter", "professional", "enterprise". |
| status | varchar(50) | PHP enum `SubscriptionStatus`: active, canceled, past_due, trialing |
| trial_ends_at | timestamp | nullable |
| ends_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

> **No free tier.** All plans are paid. Trial periods may be offered at Stripe level but there is no indefinite free tier.

---

## 6. API Routes

### 6.1 Security

Public API endpoints are vulnerable to abuse. The following protections are in place:

| Mechanism | Applies To | Detail |
|-----------|-----------|--------|
| Rate limiting (per IP) | All `/api/widget/*` endpoints | 60 requests/minute per IP (Laravel throttle middleware) |
| Rate limiting (per tenant slug) | `POST /api/widget/{slug}/conversations` | 30 new conversations/minute per tenant to prevent flooding |
| Rate limiting (per lead) | `POST /api/widget/conversations/{token}/messages` | 20 messages/minute per active conversation |
| Twilio signature validation | `POST /api/webhooks/twilio/*` | Validate `X-Twilio-Signature` header using Twilio SDK. Also check `CallSid` for idempotency — if the same `CallSid` was already processed, return 200 without side effects. |
| Signed URLs | `GET /api/missed-calls/{id}/intake` | URL tampering detected, 48h expiry |
| Session token validation | All conversation endpoints | Token must match an active, non-expired lead |
| Tenant existence check | `GET /api/widget/{slug}/config` | Invalid slugs return 404 (don't reveal whether tenant exists) |

### 6.2 Widget API (public, no auth)

| Method | Path | Purpose |
|--------|------|---------|
| POST | `/api/widget/{tenant:slug}/conversations` | Start a new conversation. Returns `lead_id` + `session_token`. |
| POST | `/api/widget/conversations/{lead:session_token}/messages` | Send a message. Returns AI response + updated qualification state. |
| POST | `/api/widget/conversations/{lead:session_token}/uploads` | Upload a photo. |
| GET | `/api/widget/conversations/{lead:session_token}` | Resume an existing conversation (signed URL). |
| GET | `/api/widget/{tenant:slug}/config` | Load tenant branding, field definitions, and localized prompts/options. Returns locale-aware content based on tenant's configured locale (defaults to industry default_locale). Widget uses this to render chip buttons with translated labels. |

### 6.3 Missed Call Webhook (Twilio)

| Method | Path | Purpose |
|--------|------|---------|
| POST | `/api/webhooks/twilio/incoming-call` | Twilio calls this when a tenant's dedicated number (or shared platform number) receives a forwarded call. **Validates Twilio signature + CallSid idempotency.** Matches tenant via `To` field (dedicated number → direct lookup on `tenants.twilio_phone_number`) or via `ForwardedFrom` (shared number → lookup on `tenant_phone_numbers`). Checks excluded numbers. Creates `missed_call` record. Sends SMS to caller. |

### 6.4 Missed Call Intake (public, signed URL)

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/api/missed-calls/{missedCall}/intake` | Validates signed URL. Creates lead, updates missed_call.lead_id. Redirects to missed-call landing page with session token. |

### 6.5 Contractor Magic Link (no auth required, token-based)

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/magic-link/{token}` | Validates magic link token (not expired, not used). Authenticates the contractor user, marks token as used, redirects to `redirect_to` URL (e.g. lead detail page). If already logged in, just redirects. |

### 6.6 Admin API

Filament manages admin UI internally. No separate admin API routes needed for MVP beyond what Filament provides.

---

## 7. Pages & Screens

### 7.1 Widget (Customer-Facing) — 4 Screens

**Screen 1: Widget Panel (Triggered State)**

The widget renders as a chat panel when triggered. It can be triggered in multiple ways:

- **Floating button** (default): Small circular button, bottom-right corner. Shows tenant's logo (from Spatie MediaLibrary `logo` collection) or chat bubble icon.
- **CTA button on page**: Any element with `data-leadgen-trigger` attribute or via JS API.
- **Programmatic trigger**: `window.LeadIntakeWidget.open()` from any link, button, or custom element.

Multiple triggers can exist on the same page. All open the same widget instance.

**Screen 2: Chat Panel (Conversation)**
- Slides up from bottom on mobile (70% screen height). Side panel on desktop (380px wide).
- Header: tenant business name (`tenants.name`), close button (X).
- Body: scrollable message list.
  - Assistant messages: left-aligned, gray bubble. Typing indicator (3-dot animation) while waiting for AI response.
  - User messages: right-aligned, tenant-primary-color bubble.
  - When AI asks a question that has `field_type: select`, the widget renders **tappable chips/buttons** instead of requiring the user to type (e.g. roof type chips: "Tile" | "Slate" | "Metal" | "Asbestos" | "Other").
  - Photo attachments rendered inline as thumbnails.
- Footer: text input + send button + camera/attachment icon (paperclip).
- Camera icon: opens device camera on mobile, file picker on desktop.
- Submits on Enter or send button tap.
- Shows loading/uploading states.

**Screen 3: Completion Screen**
- Triggered when `is_complete: true` in API response.
- Assistant displays final message.
- Checkmark animation (green circle with white check).
- Input area and send button hidden.
- "Close" button at bottom.

**Screen 4: Error/Retry States**
- Rate limited: "Está a enviar mensagens demasiado rápido. Por favor aguarde um momento."
- Network disconnected: "Ligação perdida. Toque para tentar novamente." with retry button.
- AI timeout (10s+): "Um momento, ainda estou a pensar..." with option to retry.
- Upload failure: "Não foi possível enviar o ficheiro. Tente novamente."
- Invalid/expired session: "Esta sessão expirou. Por favor inicie uma nova conversa." with restart button.

### 7.2 Missed Call Landing Page (1 Screen)

**Screen 5: Missed Call Landing**
- Opens from SMS link on mobile browser.
- Simple branded page: tenant logo, business name.
- Title: "[Business Name] recebeu a sua chamada"
- Subtitle: "Estamos a ajudar outro cliente neste momento. Responda a algumas perguntas rápidas e entraremos em contacto mais depressa."
- Large CTA button: "Começar" → launches widget in full-page mode.
- One-page only. No navigation, no footer. Pure conversion.

### 7.3 Admin Panel (Filament) — 11 Screens

**Screen 6: Dashboard**
- Metrics cards across top (row of 4):
  - Leads received (today / all-time)
  - Leads completed (rate %)
  - Average qualification time (minutes:seconds)
  - Qualified leads awaiting delivery
- Simple bar chart: leads per day (last 30 days).
- Source breakdown pie/donut: widget vs missed call vs direct link.

**Screen 7: Lead List (Resource Table)**
- Columns: Created date, Name (from lead_fields), Phone (from lead_fields), Status (colored badge), Score, Source.
- Filters: Status (select), Source (select), Date range (date picker).
- Sort by: Created date (default), Score.
- Click row → navigates to Lead Detail.
- Bulk actions: Mark as Delivered.

**Screen 8: Lead Detail**
- Header: Status badge (colored), Score badge (1-10 with color — red/yellow/green), Source badge, date.
- Section 1 — Contact Info: Name, phone, email (if any), property address.
- Section 2 — Issue Details: Field values displayed with **translated labels** based on tenant's locale (e.g. `roof_type: asbestos` shown as "Amianto" for pt locale, "Asbestos" for en). Lookup: `locales.{locale}.field_options.{field_key}.{value}`. Show "Não informado" for empty optional fields.
- Section 3 — Media Gallery: Grid of uploaded photo thumbnails via Spatie MediaLibrary. Click to enlarge (lightbox). Download button. If no media: "No photos uploaded."
- Section 4 — AI Summary: Executive summary paragraph. "Recommended Actions" bullet list. "Missing Information" list (optional fields not collected).
- Section 5 — Conversation Transcript: Full chat history in chat-bubble format (collapsed by default, expandable).
- Actions (buttons): Mark as Delivered, Send Notification Now (manual trigger), Delete (with confirmation).

**Screen 9: Tenant Settings — General**
- Editable fields: Company name.
- Read-only: Slug, created date, Twilio phone number (assigned after provisioning).
- Locale selector (pt, en).
- Industry selector dropdown (roofing only for MVP).
- Widget installation snippet with copy-to-clipboard button.

**Screen 10: Tenant Settings — Phone Numbers**
- List of registered business phone numbers (for missed call matching).
- Add new number (E.164 format validation).
- Mark one as primary.
- Delete (with confirmation — "This will stop missed call recovery for this number").

**Screen 11: Tenant Settings — Excluded Numbers**
- List of excluded phone numbers (not triggered for missed call recovery).
- Add number with optional label (e.g. "Mom", "Supplier X").
- Delete.
- Explanation text: "Calls from these numbers will NOT trigger the missed call recovery SMS."

**Screen 12: Tenant Settings — Branding**
- Logo upload (drag-and-drop or file picker). Preview of uploaded logo. Stored via Spatie MediaLibrary `logo` collection on `Tenant` model.
- Primary color picker (hex input + color picker widget).
- Greeting message textarea (the first message the AI sends to the homeowner).
- Live widget preview panel on the right showing colors + logo + greeting.

**Screen 13: Tenant Settings — Qualification**
- Pre-populated from industry defaults.
- Locale selector (pt for MVP; future: en, es, fr). Determines which locale block is used for AI prompts and field labels.
- List of required fields (from industry), non-editable (grayed out).
- List of optional fields with toggles: tenant can promote an optional field to required.
- AI greeting message textarea (override the industry default greeting for the selected locale).
- Toggle: "Require photo uploads before completion?" (default: off).
- Custom business rules section (JSON key-value or structured form):
  - Example: `{"site_visit_fee": true, "site_visit_fee_amount": 50, "free_estimate": false}` — for tenants who charge for site visits.
  - These are passed to the AI as additional context so it can inform the homeowner.

**Screen 14: Tenant Settings — Notifications**
- Email section:
  - Toggle on/off.
  - Recipient list: add/remove email addresses (dynamic repeater).
- SMS section:
  - Toggle on/off.
  - Recipient list: add/remove phone numbers (dynamic repeater).

**Screen 15: Tenant Settings — Missed Call Recovery**
- Shows dedicated Twilio landline number (if assigned) or "Using shared platform number".
- If no dedicated number: button to provision one via Twilio API.
- Shows list of registered business phone numbers (from Screen 10) — these are the numbers tenants forward from.
- Instructions: "Configure o reencaminhamento condicional do seu número comercial para o número Twilio acima. Contacte o seu operador telefónico."
- SMS template textarea (variables: `{company_name}`, `{intake_url}`).
- Toggle: enable/disable missed call recovery.

**Screen 16: Billing**
- Current plan display (Standard) with features list.
- "Manage Billing" button → opens Stripe Customer Portal in new tab.
- Invoice history table.

---

## 7.4 Subscription Plans

| Plan | Price | Includes |
|------|-------|----------|
| **Standard** (MVP) | Set in Stripe | All features: widget, AI qualification, summaries, email/SMS notifications, missed call recovery with dedicated Twilio number |

### Future multi-plan structure

When we add more plans, they will differ by **SMS volume**, not by features. All functionality is available on all plans.

| Plan | Monthly SMS |
|------|------------|
| Starter | 100 SMS |
| Professional | 500 SMS |
| Enterprise | Custom |

> Architecture supports this: `subscription_tier` column exists, billing is in Stripe, SMS usage is tracked. Adding plans later is a Stripe configuration change + UI update.

---

**Screen 17: Industry Management (Superadmin only)**
- List of industries (table).
- Create/Edit industry form:
  - Slug, name, active toggle.
  - JSON editor for full industry config: required_fields, optional_fields, field_definitions, conditional_requirements, ai_prompt, media_requirements, scoring, custom_rules, locales.
- Not visible to regular tenants. Platform-operator level.

---

## 8. Configuration Structures (JSON)

> **Design principle**: Configs are flexible JSON blobs that can grow with tenant needs. New keys can be added without migrations. The `custom_rules` section in each config allows arbitrary tenant-specific business logic.

### 8.1 Industry Config (`industries.config`)

> **Locale structure**: All translatable text (prompts, system messages, greeting, field option labels) lives under `locales.{locale}`. Business logic (required_fields, conditional_requirements, scoring) is locale-independent at the top level. Adding a new language means adding a new locale block — no code changes.

```json
{
  "default_locale": "pt",
  "required_fields": [
    "contact_name",
    "phone",
    "property_address",
    "problem_type",
    "roof_type"
  ],
  "optional_fields": [
    "email",
    "roof_age",
    "urgency",
    "insurance_claim",
    "roof_size",
    "leak_location"
  ],
  "field_definitions": {
    "roof_type": {
      "type": "select",
      "options": ["tile", "slate", "metal", "asbestos", "flat", "shingle", "other"]
    },
    "problem_type": {
      "type": "select",
      "options": ["repair", "replacement", "inspection", "leak", "emergency", "other"]
    },
    "urgency": {
      "type": "select",
      "options": ["emergency_immediate", "within_week", "within_month", "just_checking"]
    },
    "insurance_claim": {
      "type": "select",
      "options": ["yes", "no", "not_sure"]
    },
    "contact_name": { "type": "text" },
    "email": { "type": "text" },
    "phone": { "type": "text" },
    "property_address": { "type": "text" },
    "roof_age": {
      "type": "select",
      "options": ["less_than_5", "5_to_15", "15_to_30", "over_30", "not_sure"]
    },
    "roof_size": {
      "type": "select",
      "options": ["small", "medium", "large", "not_sure"]
    },
    "leak_location": { "type": "text" },
    "asbestos_removal_required": {
      "type": "select",
      "options": ["yes", "no", "not_sure"]
    }
  },
  "conditional_requirements": [
    {
      "when": { "problem_type": "replacement" },
      "require": ["roof_size"]
    },
    {
      "when": { "roof_type": "asbestos" },
      "require": ["asbestos_removal_required"]
    },
    {
      "when": { "problem_type": "repair" },
      "require": ["leak_location"]
    }
  ],
  "media_requirements": {
    "recommended": ["roof_photos", "damage_photos", "leak_area_photos"],
    "require_before_completion": false
  },
  "scoring": {
    "factors": {
      "photos_uploaded": 2,
      "urgency_provided": 1,
      "address_provided": 1,
      "project_type_known": 1,
      "insurance_claim": 1,
      "replacement_project": 2
    },
    "ranges": {
      "low": [1, 3],
      "medium": [4, 7],
      "high": [8, 10]
    }
  },
  "custom_rules": {},
  "locales": {
    "pt": {
      "ai_prompt": {
        "system": "És um assistente de admissão para serviços de telhados. O teu trabalho é recolher informações de proprietários para que um empreiteiro possa preparar um orçamento. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. Nunca dês estimativas de custos, conselhos legais ou de engenharia, nem inventes informações.",
        "greeting_message": "Olá! Estou aqui para ajudar com o seu projeto de telhado. Pode contar-me um pouco sobre o que precisa?",
        "tone": "professional_friendly",
        "response_length": "concise"
      },
      "field_prompts": {
        "roof_type": "Sabe que tipo de telhado tem atualmente?",
        "problem_type": "Que tipo de serviço de telhado precisa?",
        "urgency": "Qual a urgência desta situação?",
        "insurance_claim": "Isto é um sinistro de seguro?",
        "contact_name": "Qual é o seu nome?",
        "email": "Qual é o seu email?",
        "phone": "Qual é o melhor número de telefone para o contactar?",
        "property_address": "Qual é a morada da propriedade?",
        "roof_age": "Sabe aproximadamente quantos anos tem o telhado?",
        "roof_size": "Qual é o tamanho aproximado do telhado?",
        "leak_location": "De onde vem a infiltração?",
        "asbestos_removal_required": "O telhado de amianto precisa de remoção especial. Isto é algo que gostaria de incluir?"
      },
      "field_options": {
        "roof_type": {
          "tile": "Telha",
          "slate": "Ardósia",
          "metal": "Metal",
          "asbestos": "Amianto",
          "flat": "Plano",
          "shingle": "Shingle",
          "other": "Outro"
        },
        "problem_type": {
          "repair": "Reparação",
          "replacement": "Substituição",
          "inspection": "Inspeção",
          "leak": "Infiltração",
          "emergency": "Emergência",
          "other": "Outro"
        },
        "urgency": {
          "emergency_immediate": "Emergência — preciso de ajuda agora",
          "within_week": "Esta semana",
          "within_month": "Este mês",
          "just_checking": "Só a verificar preços"
        },
        "insurance_claim": {
          "yes": "Sim",
          "no": "Não",
          "not_sure": "Não tenho a certeza"
        },
        "roof_age": {
          "less_than_5": "Menos de 5 anos",
          "5_to_15": "5 a 15 anos",
          "15_to_30": "15 a 30 anos",
          "over_30": "Mais de 30 anos",
          "not_sure": "Não sei"
        },
        "roof_size": {
          "small": "Pequeno",
          "medium": "Médio",
          "large": "Grande",
          "not_sure": "Não sei"
        },
        "asbestos_removal_required": {
          "yes": "Sim",
          "no": "Não",
          "not_sure": "Não tenho a certeza"
        }
      }
    }
  }
}
```

> **Adding English (future)**: Add a `locales.en` block with the same structure, containing English `ai_prompt`, `field_prompts`, and `field_options`. The rest of the config (required_fields, scoring, conditional_requirements) stays unchanged. The system resolves the active locale at runtime: tenant preference → industry default_locale.

### 8.2 Tenant Branding Config (`tenants.branding_config`)

```json
{
  "primary_color": "#1a56db"
}
```

Note: Logo is handled by Spatie MediaLibrary (`logo` collection on Tenant model). `tenants.name` is on the tenants table directly. The greeting message comes from the industry config's `locales.{locale}.ai_prompt.greeting_message` (overridable via `qualification_overrides.greeting_message`). The branding config only stores visual properties.

### 8.3 Tenant Notification Config (`tenants.notification_config`)

```json
{
  "email": {
    "enabled": true,
    "recipients": ["owner@abcroofing.com", "office@abcroofing.com"]
  },
  "sms": {
    "enabled": true,
    "recipients": ["+351912345678"]
  },
  "missed_call_sms_template": "Obrigado por contactar a {company_name}. Estamos ocupados neste momento. Responda a algumas perguntas rápidas para o ajudarmos mais depressa: {intake_url}"
}
```

### 8.4 Tenant Qualification Overrides (`tenants.qualification_overrides`)

```json
{
  "additional_required_fields": ["insurance_claim"],
  "greeting_message": "Welcome to ABC Roofing! Tell us about your roof project and we'll get back to you quickly.",
  "require_photos": true,
  "custom_rules": {
    "site_visit_fee": true,
    "site_visit_fee_amount": 50,
    "free_estimate": false,
    "service_area_zip_codes": ["78701", "78702", "78703"]
  }
}
```

Merged on top of the industry config at runtime: **Customer overrides industry. Industry overrides global.** The `custom_rules` object is passed to the AI as additional context (e.g. "This contractor charges a $50 site visit fee — let the homeowner know").

---

## 9. User Stories (Detailed, With Acceptance Criteria)

### US-01: Homeowner qualifies via website widget

**Actor**: Homeowner

1. Visits contractor's website (WordPress, Shopify, custom).
2. Sees floating chat button (bottom-right corner).
3. Taps button. Chat panel opens.
4. System displays tenant-branded greeting from `ai_prompt.greeting_message` (or overridden version).
5. Homeowner types or taps chips to answer questions.
6. System (AI): acknowledges, asks a conversational follow-up. For select-type fields, AI presents options conversationally; the widget also renders tappable chip buttons.
7. Back-and-forth continues. AI asks one question at a time.
8. AI occasionally requests photos: "Could you upload a couple of photos of the damaged area?"
9. Homeowner taps camera icon, takes photos, uploads. Stored via Spatie MediaLibrary.
10. When all required fields are collected: system auto-triggers completion.
11. System generates summary + score → queues notification → delivers lead.
12. Homeowner sees confirmation screen with checkmark animation.

**Acceptance Criteria**:
- Full qualification completes in under 3 minutes of the homeowner's time.
- All required fields collected before completion (platform-enforced).
- Photos stored via Spatie MediaLibrary on local disk, associated with lead.
- Select-type fields render as tappable chips in the widget.
- Contractor receives email notification within 60 seconds of completion.
- Chat history is persisted and retrievable.

### US-02: Homeowner resumes an incomplete conversation

**Actor**: Homeowner

1. Starts qualification, answers 3 questions, closes browser.
2. Reopens the link (bookmarked widget page or from SMS).
3. System validates signed URL → restores conversation at the exact state they left.
4. AI picks up where it left off (does NOT re-ask already-collected information).
5. Completes qualification.

**Acceptance Criteria**:
- Signed URL is valid for 48 hours after last message.
- Conversation state (messages + fields) is fully restored.
- AI does not re-ask already-collected fields.
- Expired signed URL shows clear "session expired" message with restart option.

### US-03: Contractor receives qualified lead via email

**Actor**: Contractor

1. Homeowner completes qualification.
2. System generates AI summary.
3. System sends email to all configured recipients.
4. Email contains: customer name, phone, address, issue summary, urgency, score, photo links.
5. Contractor reads email, decides next action.

**Acceptance Criteria**:
- Email arrives within 60 seconds of lead completion.
- Email contains all structured fields and working photo links (signed temporary URLs).
- Email renders correctly on mobile and desktop.
- If email fails, notification status is "failed" with error logged.

### US-04: Contractor receives qualified lead via SMS with magic link

**Actor**: Contractor

1. Homeowner completes qualification.
2. System sends SMS to configured phone numbers.
3. SMS body: "[Customer Name], [Urgency]. Score: X/10. [magic link to lead detail]"
4. Contractor taps the magic link on their phone.
5. System validates the magic link token:
   - If not expired and not used: authenticates the contractor, marks token as used, redirects to lead detail.
   - If contractor is already logged in on that device: skips auth, directly shows lead detail.
   - If expired/used: shows error with link to login manually.

**Acceptance Criteria**:
- SMS arrives within 60 seconds.
- Magic link auto-authenticates the contractor — no password entry required.
- Magic link works only once (token marked as used).
- Magic link expires after 7 days.
- SMS respects 160-char limit; URL shortened if needed.
- If already logged in, just redirects to lead without re-auth.

### US-05: Missed call recovery (end-to-end)

**Actor**: Homeowner (caller), Contractor (recipient)

1. Homeowner dials contractor's business phone number.
2. Contractor doesn't answer (busy, on-site, after-hours).
3. Conditional call forwarding → call forwarded to tenant's dedicated Twilio landline number.
4. Twilio webhook fires → `POST /api/webhooks/twilio/incoming-call`.
5. System validates Twilio signature + CallSid idempotency.
6. System matches tenant via `To` field → lookup on `tenants.twilio_phone_number`.
7. System checks `tenant_excluded_numbers`: if caller's number is excluded → log, ignore, return 200.
8. System creates `missed_call` record with `matched_by = dedicated_number`.
9. System sends SMS (via Twilio) to the homeowner with signed intake URL.
10. Homeowner taps the link → Missed Call Landing Page → taps "Começar" → qualification begins.
11. Lead delivered to contractor (same as US-03/US-04).

**Acceptance Criteria**:
- SMS sent to homeowner within 15 seconds of missed call.
- Excluded numbers do NOT trigger SMS.
- Tenant identified via dedicated Twilio number (`To` field) — 100% reliable.
- Intake link is a signed URL valid for 48 hours.
- `missed_call.lead_id` is populated when the homeowner starts qualification.
- Full round-trip: missed call → SMS → qualification → lead delivered.

### US-06: Contractor onboards to the platform

**Actor**: Contractor (new customer)

1. Visits platform marketing site (out of scope; assumed to exist).
2. Clicks "Start Free Trial" or "Sign Up" → redirected to Stripe Checkout.
3. Enters: company name, business phone number, selects industry (Roofing).
4. Completes Stripe checkout → tenant + user + subscription created.
5. Redirected to Filament admin panel (auto-logged-in after Stripe callback).
6. Lands on Dashboard (empty state — "No leads yet. Install your widget to get started.").
7. Guided onboarding checklist appears:
   - Step 1: Add business phone numbers (for missed call matching).
   - Step 2: Copy widget script → paste into website.
   - Step 3: Provision Twilio number. Configure call forwarding from business number to Twilio number.
   - Step 4: Add notification recipients (email/SMS).
8. Contractor copies script, pastes into their website.
9. Done. Widget is live.

**Acceptance Criteria**:
- Complete onboarding from registration to widget-installed in under 10 minutes.
- No manual support required at any step.
- Widget works immediately after script paste.
- Stripe subscription created correctly. Tenant + user created after successful payment.
- Dedicated Twilio number provisioned during onboarding.

### US-07: Contractor reviews and manages leads in Filament

**Actor**: Contractor

1. Logs into admin panel (email + password) or clicks magic link from SMS.
2. Sees Dashboard with real metrics matching their tenant data.
3. Navigates to Leads list.
4. Filters by status "Qualified".
5. Clicks a lead row → Lead Detail opens.
6. Reviews AI summary, photos, conversation transcript.
7. Clicks "Mark as Delivered".
8. Lead status changes to "Delivered" — disappears from "Qualified" filter.

**Acceptance Criteria**:
- Lead list shows ONLY the logged-in tenant's leads (tenant scoping verified).
- Photos displayed inline, clickable for lightbox enlarge.
- Conversation transcript is readable, shows user + assistant messages in order.
- "Mark as Delivered" updates status immediately.
- Cannot view other tenants' leads even by guessing IDs.

### US-08: Tenant generates a direct intake link

**Actor**: Contractor

1. In the admin panel, navigates to Leads → "Generate Intake Link".
2. System generates a unique signed URL for the tenant.
3. Contractor copies the link and shares it manually (SMS, email, social media, WhatsApp).
4. Homeowner opens the link → Missed Call Landing Page → taps "Start" → qualification begins.
5. Lead is created with `source = direct_link`.

**Acceptance Criteria**:
- Direct link is a signed URL valid for 30 days.
- Lead source correctly tracked as `direct_link`.
- Same qualification flow as widget/missed call.

---

## 10. Service Layer

| Service | File | Responsibility |
|---------|------|---------------|
| `TenantService` | `app/Services/TenantService.php` | Resolve current tenant, load config, branding, scoping. Match phone numbers to tenants. |
| `LeadService` | `app/Services/LeadService.php` | Create lead, transition status, generate session tokens |
| `ConversationOrchestrator` | `app/Services/ConversationOrchestrator.php` | **Core engine.** Receive message → load context → call AI → extract → validate → respond → persist. All queued. |
| `QualificationEngine` | `app/Services/QualificationEngine.php` | Track required/optional fields, validate, detect missing, determine completion. Platform-owned logic — AI never decides this. |
| `IndustryConfigEngine` | `app/Services/IndustryConfigEngine.php` | Load industry definition, resolve config hierarchy (global → industry → customer → runtime). Load field definitions. |
| `StructuredExtractor` | `app/Services/StructuredExtractor.php` | Parse AI response, extract fields with confidence scores, normalize values to canonical form |
| `MediaService` | `app/Services/MediaService.php` | Wrap Spatie MediaLibrary operations. Handle uploads, generate temporary signed URLs for access. |
| `SummaryService` | `app/Services/SummaryService.php` | AI-powered: generate executive summary + recommended actions from structured fields |
| `LeadScoringService` | `app/Services/LeadScoringService.php` | Rule-based scoring using industry config scoring factors |
| `NotificationService` | `app/Services/NotificationService.php` | Dispatch email (bentonow) and SMS (Twilio) via provider abstractions. Generate magic links for SMS notifications. |
| `MagicLinkService` | `app/Services/MagicLinkService.php` | Generate, validate, and consume magic link tokens for contractor authentication |
| `RateLimitService` | `app/Services/RateLimitService.php` | Throttle public API endpoints per IP, per tenant, per lead |

---

## 11. Provider Abstractions (Contracts)

| Interface | File | Method | Initial Implementation |
|-----------|------|--------|----------------------|
| `SmsProvider` | `app/Contracts/SmsProvider.php` | `send(string $to, string $message): SmsResult` | `TwilioSmsProvider` |
| AI Provider | Via `laravel/ai` SDK (built-in) | — | DeepSeek |

Email uses Laravel's native `Mail` facade + custom bentonow transport. No custom contract needed — standard Laravel mail driver swap. File storage uses Laravel's `Storage` facade with `media` disk (local).

**Email sender**: `noreply@oursaas.com` with `Reply-To` set to the tenant's primary notification email. Simple plain-text email for MVP — no complex HTML templates.

---

## 12. Queue Jobs

| Job | Trigger | Queue Name |
|-----|---------|------------|
| `ProcessMessageJob` | User sends message via widget | `conversations` |
| `ExtractFieldsJob` | After AI response received | `extraction` |
| `GenerateSummaryJob` | Lead marked as qualified | `summaries` |
| `ScoreLeadJob` | Lead marked as qualified | `scoring` |
| `SendLeadNotificationJob` | Lead marked as qualified | `notifications` |
| `ProcessMediaUploadJob` | File uploaded via widget | `media` |
| `HandleIncomingCallJob` | Twilio webhook received | `webhooks` |
| `SendMissedCallSmsJob` | Missed call matched to tenant | `webhooks` |

All jobs are queued using the SQLite queue connection. No AI calls, image processing, or notifications happen synchronously in HTTP requests.

---

## 13. AI Flow Per Message (Detailed)

```
1. POST /api/widget/conversations/{token}/messages
   Body: { "message": "I have a leaking tile roof in Austin" }
   (Rate-limited: max 20/min per lead)

2. ConversationOrchestrator (inside ProcessMessageJob):
   a. Load lead by session_token + tenant scope
   b. Load conversation_history (last 10 messages)
   c. Load qualification_state (collected fields, missing fields, confidence)
   d. Load tenant + industry_config
   e. Load field_definitions (which fields are select vs text, options)
   f. Resolve config hierarchy: global → industry → customer overrides
   g. Resolve locale: tenant preference → industry default_locale → "pt"
   h. Load localized strings from config.locales.{locale}: ai_prompt, field_prompts, field_options
   i. Load custom_rules (e.g. site visit fee info) for AI context
   j. Build AI prompt:
      - System: locales.{locale}.ai_prompt.system
      - Industry context: industry rules + conditional requirements
      - Custom rules: tenant.qualification_overrides.custom_rules
      - Field definitions: types and localized option labels for hinting
      - Qualification state: collected=[], missing=[contact_name, phone, address, problem_type, roof_type]
      - History: [] (first message)
      - User message: "Tenho uma infiltração no telhado de telha em Lisboa"
   k. Send to DeepSeek via laravel/ai SDK
   l. AI responds (in Portuguese, matching the system prompt locale):
      - Conversational reply: "Percebido, uma infiltração num telhado de telha em Lisboa. Pode dizer-me o seu nome?"
      - Tool call: update_field(problem_type="repair", roof_type="tile", property_address="Lisboa")
   m. StructuredExtractor parses tool calls, normalizes values, assigns confidence
   n. QualificationEngine:
      - Updates lead_fields with normalized values + field_type from field_definitions
      - Checks conditional rules: problem_type=repair → leak_location becomes required
      - Updates missing: [contact_name, phone, leak_location]
   o. Persist: conversation_message (assistant), lead_fields (×3), updated qualification state
   p. Return to widget:
      {
        "reply": "Percebido, uma infiltração num telhado de telha em Lisboa. Pode dizer-me o seu nome?",
        "is_complete": false,
        "progress": { "collected": 3, "required": 6 },
        "next_field": {
          "key": "contact_name",
          "type": "text",
          "prompt": "Qual é o seu nome?"
        },
        "lead": { "id": 42, "session_token": "abc123..." }
      }
```

---

## 14. Widget API Response Format

All responses follow this shape:

```json
{
  "reply": "Do you know what type of roof you currently have?",
  "is_complete": false,
  "progress": {
    "collected": 2,
    "required": 6
  },
  "next_field": {
    "key": "roof_type",
    "type": "select",
    "prompt": "Sabe que tipo de telhado tem atualmente?",
    "options": [
      { "value": "tile", "label": "Telha" },
      { "value": "slate", "label": "Ardósia" },
      { "value": "metal", "label": "Metal" },
      { "value": "asbestos", "label": "Amianto" },
      { "value": "flat", "label": "Plano" },
      { "value": "shingle", "label": "Shingle" },
      { "value": "other", "label": "Outro" }
    ]
  },
  "lead": {
    "id": 42,
    "session_token": "abc123..."
  }
}
```

When `next_field.type` is `select`, the widget renders tappable chip buttons. When `text`, it shows the text input. The `options` array comes from `field_definitions` in the industry config.

When complete:

```json
{
  "reply": "Obrigado! Já temos tudo o que precisamos. A ABC Roofing entrará em contacto em breve.",
  "is_complete": true,
  "lead": {
    "id": 42,
    "session_token": "abc123..."
  }
}
```

Error response (rate limited):

```json
{
  "error": "rate_limited",
  "message": "Está a enviar mensagens demasiado rápido. Aguarde um momento.",
  "retry_after_seconds": 30
}
```

---

## 15. Widget Embed & API

### 15.1 Embed Code

```html
<script src="https://app.leadgen.com/js/widget.js" data-tenant="acme-roofing"></script>
```

- `data-tenant`: the tenant slug (required).
- The script registers a custom element `<lead-intake-widget>` and exposes `window.LeadIntakeWidget`.
- On load, the widget fetches config from `/api/widget/{slug}/config` which returns locale-aware branding, field definitions, and prompts.

### 15.2 Floating Button (Default Behavior)

By default, the widget renders a floating button in the bottom-right corner. No additional markup needed.

To disable the default button and use only programmatic triggers:
```html
<script src="..." data-tenant="acme-roofing" data-floating-button="false"></script>
```

### 15.3 CTA / Button Trigger

Any element with `data-leadgen-trigger` will open the widget when clicked:

```html
<a href="#" data-leadgen-trigger>Get a Free Quote</a>
<button data-leadgen-trigger>Start Roof Inspection</button>
```

### 15.4 Programmatic JS API

```js
// Open the widget
window.LeadIntakeWidget.open();

// Close the widget
window.LeadIntakeWidget.close();

// Toggle the widget
window.LeadIntakeWidget.toggle();

// Check if widget is open
window.LeadIntakeWidget.isOpen();

// Open widget with a pre-filled message (user doesn't see it sent, but AI responds to it)
window.LeadIntakeWidget.open({ prefilledMessage: "I need a roof inspection" });
```

This allows:
- A "Get Quote" CTA anywhere on the page
- Multiple buttons on the same page (all trigger the same widget)
- A nav menu link that opens the widget
- A hero section button that opens the widget
- Any custom integration the tenant needs

### 15.5 Widget Initialization Flow

```
1. Page loads with <script> tag
2. Widget fetches GET /api/widget/{slug}/config
   Response includes:
   - tenant name, logo URL, primary color
   - locale (from tenants.locale)
   - field_definitions with localized prompts and option labels
3. If data-floating-button != "false": renders floating button
4. Binds click handlers to all [data-leadgen-trigger] elements
5. Exposes window.LeadIntakeWidget for programmatic use
6. Widget ready — renders floating button (if enabled)
7. If API unreachable or tenant slug invalid: **silently fails**. No JS errors. No floating button appears. Page continues working normally.

---

## 16. Missed Call Recovery Flow

**Architecture**: Per-tenant dedicated Twilio landline numbers (+351 21x / 22x) as default. Shared platform number with `ForwardedFrom` matching as fallback.

### Option A: Dedicated per-tenant number (default, recommended)

```
1. During onboarding, tenant gets a dedicated Twilio landline number.
   - Provisioned via Twilio API (search + buy + configure webhook) or manually in Twilio Console.
   - Stored in tenants.twilio_phone_number and tenants.twilio_phone_sid.
   - All numbers are +351 21x/22x (landline, cheaper than mobile).
   - Tenant sets up conditional call forwarding: their business number → this Twilio number.
2. Homeowner calls tenant's business number.
3. Tenant doesn't answer → forwarded to tenant's dedicated Twilio number.
4. Twilio webhook → POST /api/webhooks/twilio/incoming-call
   - `From`: +351-923-456-789 (homeowner)
   - `To`: +351-210-000-001 (tenant's dedicated number)
5. System matches tenant: SELECT * FROM tenants WHERE twilio_phone_number = `To`
   → DIRECT match, 100% reliable, no carrier dependency.
6. Idempotency check via CallSid.
7. Check excluded numbers → if excluded, skip.
8. Create missed_call (matched_by = dedicated_number).
9. Send SMS to caller → intake link → qualification → lead delivered.
```

### Option B: Shared platform number (fallback)

```
1. Tenant opts out of dedicated number (or not on Professional tier).
2. Tenant sets up forwarding: business number → platform shared number.
3. Webhook fires. System attempts ForwardedFrom matching against tenant_phone_numbers.
4. If ForwardedFrom empty → logged as unmatched.
5. matched_by = forwarded_from.
```

### Tenant chooses at onboarding

- By default, every tenant gets a dedicated Twilio landline number.
- Tenant can opt to use the shared platform number instead (less reliable).

### Number provisioning

- **Auto (Twilio API)**: System calls Twilio API to search available +351 landline numbers, buy one, configure webhook URL, assign to tenant.
- **Manual (Twilio Console)**: Admin buys number in Twilio Console, sets webhook to our endpoint, enters number + SID in tenant settings.
- All numbers landline (+21x/+22x) — receiving only, never answering. Lowest cost.

---

## 17. MVP Scope Boundaries

### Included in MVP
- Website widget (Web Component, single JS file, mobile-first)
- AI conversation with DeepSeek (roofing industry only)
- Field type system: text fields + select/chip fields
- Structured field extraction with confidence scores
- Photo upload via Spatie MediaLibrary (images only; PNG, JPG, WEBP; max 10MB; local disk)
- Lead qualification with required + optional + conditional fields
- Rule-based lead scoring (1-10)
- AI-generated lead summary (executive summary + recommended actions)
- Email notification to contractor (bentonow.com)
- SMS notification to contractor with magic link auth (Twilio)
- Missed call recovery: dedicated Twilio landline number, tenant phone matching, excluded numbers
- Multi-tenant Filament admin (Dashboard, Leads, Settings, Billing)
- Stripe subscription: single Standard plan (no free tier)
- Dashboard with basic metrics
- API rate limiting (IP + tenant + lead level)
- Twilio webhook signature validation
- Flexible JSON config with custom_rules for tenant-specific logic

- Widget: silently fail if invalid slug/API unreachable

### Excluded from MVP (Future)
- HVAC, plumbing, solar, landscaping industries (roofing only)
- Video uploads
- PDF uploads
- WhatsApp notifications
- Google OAuth for admin login (email/password only)
- CRM integrations (Zapier, webhooks, external API)
- Multilingual qualification (Portuguese only for MVP)
- Voice intake
- AI visual image analysis
- Advanced analytics/reporting
- Tenant self-service industry switching

> **Data retention**: Lead data retained for the legal period required by Portuguese law. Account cancellation stops new lead collection; existing data retained for legal compliance period then purged.

---

## 18. Testing Strategy

### Feature Tests (Pest)

| Test | What It Verifies |
|------|-----------------|
| `TenantScopingTest` | Every query is tenant-scoped. Cross-tenant access returns 404. Leads, media, messages, phone numbers — all scoped. |
| `WidgetApiTest` | Start conversation → returns session_token. Send message → returns AI reply with next_field info. Upload file → stored + linked. Resume via signed URL → restored state. Invalid token → 404. |
| `WidgetRateLimitTest` | Per-IP limit enforced. Per-tenant conversation limit enforced. Per-lead message limit enforced. Returns 429 with retry_after. |
| `ConversationOrchestratorTest` | Full message → extraction → validation → completion cycle. AI mocked, platform logic tested. Select fields return options, text fields don't. |
| `QualificationEngineTest` | Required fields tracked. Missing fields detected. Conditional rules applied. Field types validated. Completion only when all satisfied. |
| `MissedCallRecoveryTest` | Webhook received → Twilio signature validated → excluded number skipped → tenant matched via tenant_phone_numbers → missed_call created → SMS dispatched → intake URL valid → lead created. Unmatched → ignored. |
| `NotificationTest` | Lead qualified → email + SMS jobs dispatched. SMS includes magic link. Correct recipients. Correct template variables. |
| `MagicLinkTest` | Token generated → valid link authenticates → redirects correctly → already-logged-in skips auth → expired token fails → used token fails. |
| `LeadScoringTest` | All scoring factors sum correctly. Score in 1-10 range. |
| `SummaryGenerationTest` | AI summary generated from structured fields. Contains all collected data. Custom rules affect output. |
| `IndustryConfigTest` | Config hierarchy resolves: global → industry → tenant override. Field definitions merge. Custom rules preserved. |
| `StripeWebhookTest` | Checkout completed → tenant + user + subscription created. Subscription updated/canceled events handled. |

### Unit Tests (Pest)

| Test | What It Verifies |
|------|-----------------|
| `StructuredExtractorTest` | Parses tool call output, extracts fields, confidence scores, edge cases. |
| `IndustryConfigEngineTest` | Loading, merging, override resolution. Field definition lookup. |
| `SmsProviderTest` | `TwilioSmsProvider` correctly formats and sends. Mock Twilio SDK. |
| `TenantServiceTest` | Tenant resolution, phone number matching, config loading. |
| `RateLimitServiceTest` | Throttle calculations for IP, tenant, lead levels. |

### Browser Tests (Pest + Playwright/Laravel Dusk)

| Test | What It Verifies |
|------|-----------------|
| Widget renders | Script tag loads, floating button appears, branding colors correct. |
| Full conversation flow | Open widget → type message → see AI reply → see chip buttons for select fields → tap chips → see completion screen. |
| Photo upload | Camera/file picker → upload → thumbnail appears in chat. |
| Missed call landing page | Page renders with tenant branding → "Start" button → widget opens. |

### Coverage Goal
- **100%** of service classes.
- **100%** of API endpoints.
- **100%** of critical paths: conversation, qualification, missed call recovery, notifications, magic links.
- Edge cases: rate limiting, network failures, AI timeouts, upload failures, invalid/expired signed URLs, expired magic links, excluded numbers.

---

## 19. Session Workflow (For Developers)

1. Read this `developing.md` first — every session.
2. Break down the task against the architecture defined here.
3. When uncertain about Filament or Livewire APIs, consult the documentation summaries in:
   - `developing/filament/` — Filament 5 reference
   - `developing/livewire/` — Livewire 4 reference
   - Also use `search-docs` (Boost MCP) for live documentation queries.
4. Write tests alongside code — TDD where practical.
5. Run `vendor/bin/pint --dirty --format agent` after PHP changes.
6. Run `php artisan test --compact` to verify nothing broke.
7. Use `php artisan make:` commands with `--no-interaction` for new files.
8. Never create documentation files unless explicitly asked.
9. All enum-like values go in `app/Enums/` as PHP 8.5 backed enums. Database columns store the string value.
10. All filesystem operations use Laravel's `Storage` facade with the `media` disk. No hardcoded paths.
