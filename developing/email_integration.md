# Email Integration — Architecture & Implementation Plan

> **Status**: PLANNING — not yet implemented.
> **Constraint**: No Google APIs, no Microsoft Graph, no OAuth restricted scopes.
> All providers use standard IMAP (inbound) + SMTP (outbound) with app passwords.

---

## 1. What We're Building

A tenant connects their email account (Google, Microsoft, or custom) to the platform. Once connected:

- **Inbound**: Emails from known leads are captured and added to the lead's conversation history.
  The tenant gets an SMS with a short link to view the new email.
- **Lead discovery**: If the tenant sets a folder to watch, emails arriving there get AI-parsed.
  Unknown senders automatically become new leads.
- **Outbound**: Emails to leads come from the tenant's own account, not a platform address.

---

## 2. Why IMAP + App Passwords

Any approach that reads or sends email on a user's behalf requires Google/Microsoft to classify
the app's OAuth scope as "restricted" — triggering a CASA Tier 2 security assessment ($15k-$75k).
This applies equally to the Gmail REST API, Microsoft Graph mail scopes, and IMAP via XOAUTH2.

**The free path**: The tenant generates an **app password** in their account settings
(a 16-character credential that bypasses OAuth entirely). We store it encrypted and use it
for standard IMAP/SMTP connections. This is what products like Help Scout, Front, and Close.com
do for Gmail/Outlook integration.

| Provider | IMAP host | IMAP port | SMTP host | SMTP port |
|---|---|---|---|---|
| Google | `imap.gmail.com` | 993 | `smtp.gmail.com` | 587 |
| Microsoft | `outlook.office365.com` | 993 | `smtp.office365.com` | 587 |
| Custom | user-defined | user-defined | user-defined | user-defined |

---

## 3. Architecture

### 3.1 One code path for all providers

Google, Microsoft, and custom all use the same IMAP/SMTP protocol. The only difference is
pre-filled host/port defaults. No `EmailProvider` interface with separate adapters needed —
a single `ImapService` + dynamic SMTP mailer handles everything.

### 3.2 Plan-gated inbox limits

Number of connectable email accounts is capped by subscription plan. The `EnsureActiveSubscription`
middleware already checks `isServiceActive()` — we extend it to also check account count against
the plan's `max_email_accounts` (starter: 1, professional: 3, enterprise: unlimited).
The Filament UI shows an upgrade prompt when the limit is reached.

### 3.3 Polling (no push)

With no API access, there is no push notification mechanism. A scheduled command (`email:poll`)
runs every 2 minutes, connects via IMAP, fetches unseen messages, and dispatches processing jobs.

Polling is lightweight — `SEARCH UNSEEN` + `FETCH` — and scales fine for the expected volume
(dozens of tenants, not thousands). Each account sync is a separate queued job.

### 3.4 Queue everything

All processing is queued (SQLite queue DB, consistent with existing architecture):

| Job | Trigger | Does |
|---|---|---|
| `PollInboxJob` | Scheduler (every 2 min) | IMAP connect → fetch unseen → chain processing jobs |
| `ProcessIncomingEmailJob` | `PollInboxJob` | Match lead, store message, notify tenant |
| `SendEmailFromTenantJob` | System (manual/automated) | Send via tenant's SMTP |
| `AiParseEmailForLeadCreationJob` | `ProcessIncomingEmailJob` | AI extraction → create lead from unknown sender |

### 3.5 Credential storage

App passwords are encrypted at rest via `Crypt::encryptString()` (AES-256-CBC).
No refresh tokens, no expiry tracking — app passwords don't expire unless revoked by the user.

---

## 4. Database Schema

### 4.1 `tenant_email_accounts`

```php
Schema::create('tenant_email_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('provider', 20);             // 'google', 'microsoft', 'custom'
    $table->string('email', 255);
    $table->string('name', 255)->nullable();    // display name
    $table->text('app_password')->nullable();   // encrypted
    $table->json('imap_config')->nullable();    // {host, port, encryption} — pre-filled for google/microsoft
    $table->json('smtp_config')->nullable();    // {host, port, encryption}
    $table->string('status', 20)->default('active');   // active, error, disconnected
    $table->string('watch_folder', 100)->nullable();   // IMAP folder for lead discovery
    $table->boolean('auto_create_leads')->default(false);
    $table->timestamp('last_synced_at')->nullable();
    $table->unsignedBigInteger('last_synced_uid')->nullable(); // IMAP UID for incremental sync
    $table->string('last_error', 500)->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['tenant_id', 'email']);
});
```

### 4.2 `lead_email_messages`

```php
Schema::create('lead_email_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tenant_email_account_id')->nullable()->constrained()->nullOnDelete();
    $table->string('direction', 10);              // 'inbound', 'outbound'
    $table->unsignedBigInteger('message_uid')->nullable(); // IMAP UID for dedup
    $table->string('message_id_header', 500)->nullable(); // RFC 2822 Message-ID
    $table->string('in_reply_to_header', 500)->nullable(); // threading
    $table->text('references_header')->nullable();        // full thread ancestry
    $table->string('subject', 500)->nullable();
    $table->text('body_text')->nullable();
    $table->text('body_html')->nullable();
    $table->string('from_address', 255);
    $table->string('from_name', 255)->nullable();
    $table->json('to_addresses')->nullable();
    $table->json('cc_addresses')->nullable();
    $table->json('attachment_media_ids')->nullable();    // MediaLibrary media IDs
    $table->json('raw_headers')->nullable();       // parsed headers for AI context
    $table->json('ai_extracted_fields')->nullable();
    $table->timestamp('received_at')->nullable();
    $table->timestamps();

    $table->unique(['tenant_email_account_id', 'message_uid']);
    $table->index('lead_id');
    $table->index('from_address');
});
```

---

## 5. Core Jobs

### 5.1 `PollInboxJob`

Dispatched by `email:poll` for each active account every 2 minutes.

```
1. Connect to IMAP server (host/port/encryption from imap_config, password decrypted)
2. SELECT the inbox (or watch_folder if set)
3. SEARCH UID > last_synced_uid (or UNSEEN if first sync)
4. For each new message:
   a. FETCH FLAGS, INTERNALDATE, BODY[HEADER], BODY[TEXT]
   b. Parse headers → from, to, cc, subject, Message-ID, received_at
   c. Dispatch ProcessIncomingEmailJob
5. Update last_synced_uid, last_synced_at
6. On connection failure: update last_error, mark as 'error' after 3 consecutive failures
```

### 5.2 `ProcessIncomingEmailJob`

```
1. Dedup: skip if (tenant_email_account_id, message_uid) already exists
2. Find Lead by from_address
3. If lead found:
   a. Store in lead_email_messages (direction=inbound)
   b. If notify_on_new_email → SMS: "Novo email de {name}: {subject}" + short link
4. If lead NOT found AND auto_create_leads is true:
   a. Queue AiParseEmailForLeadCreationJob
   b. AI → {name, phone, service_type, urgency, address, message_summary}
   c. Create Lead(source=Email, status=New)
   d. Store message linked to new lead
5. Otherwise: skip (unknown sender to general inbox)
```

### 5.3 `SendEmailFromTenantJob`

```
1. Find tenant's active email account
2. Dynamically configure mailer with tenant's SMTP config + decrypted password
3. Send via Mail::mailer('tenant_{id}') with From: tenant's email
4. Store in lead_email_messages (direction=outbound)
5. Fall back to system default mailer if no connected account
```

### 5.4 AI email parsing

```
System prompt: "Extract structured lead information from this email. Never invent data.
Respond ONLY with JSON: {name, phone, email, service_type, urgency, address, message_summary}"

Email headers + body → AI (DeepSeek, small model) → structured JSON → Lead fields
```

---

## 6. Scheduled Command

```
php artisan email:poll
```

Registered in `routes/console.php`, runs every 2 minutes. Dispatches `PollInboxJob` for each
active account, staggered by 5 seconds to avoid connection bursts.

---

## 7. Filament UI

**Connect account form**:
- Provider selector (Google / Microsoft / Custom)
- For Google/Microsoft: instructions with link to app password generation page
- For Custom: host/port/encryption fields
- Email + app password fields
- "Testar ligação" button → validates IMAP connection

**Per-account settings**:
- Pasta para novos leads (IMAP folder name)
- Criar leads automaticamente (toggle)
- Notificar por SMS (toggle + recipients)

**Connected accounts list**: status badge, last sync time, provider icon, disconnect action.

**Lead detail**: Filament relation manager showing email thread with the lead.

---

## 8. Security

| Concern | Mitigation |
|---|---|
| App password at rest | Encrypted via `Crypt::encryptString()` (AES-256-CBC) |
| App password in transit | IMAP/SMTP over TLS (ports 993/587). Decrypted only at connection time in queue worker |
| User revokes access | Connection fails → status marked `error` → tenant notified via SMS |
| Email content privacy | Processed in queue jobs only. Never logged. AI parsing is stateless |

---

## 9. Implementation Phases

### Phase 1: Foundation (3-4 hours)
- [ ] `tenant_email_accounts` migration + model + factory
- [ ] `lead_email_messages` migration + model + factory
- [ ] `ImapService` (connect, fetch unseen, parse headers/body, UID tracking)
- [ ] Dynamic SMTP mailer configuration
- [ ] `email:poll` command + `PollInboxJob`

### Phase 2: Core processing (3-4 hours)
- [ ] `ProcessIncomingEmailJob` (match lead, store, notify)
- [ ] `SendEmailFromTenantJob` (send via tenant SMTP)
- [ ] Dedup via message_uid unique constraint
- [ ] Error handling + status tracking

### Phase 3: AI & Lead discovery (2-3 hours)
- [ ] `AiParseEmailForLeadCreationJob`
- [ ] Watch folder logic
- [ ] Auto lead creation flow

### Phase 4: Filament UI (2-3 hours)
- [ ] Email account connection form + IMAP validation
- [ ] Per-account settings (watch folder, auto-create, SMS notify)
- [ ] Email conversation view in Lead detail

### Phase 5: Polish (2 hours)
- [ ] SMS notifications for new emails
- [ ] Short link generation for email notifications
- [ ] Tests

---

## 10. Decided Questions

### 10.1 Multiple inboxes (plan-gated)

Starter plan → 1 inbox. Professional plan → up to 3. Enterprise → unlimited.
Enforced via `EnsureActiveSubscription` middleware check on the connect/management endpoints.
The Filament UI hides the "Conectar conta" button when the limit is reached and shows an
upgrade prompt.

### 10.2 Attachments

Email attachments (photos, PDFs, documents) are stored as MediaLibrary media on the lead.
`ProcessIncomingEmailJob` fetches attachment parts during IMAP `FETCH`, uploads via
`$lead->addMedia()`, and stores a reference in `lead_email_messages.attachment_media_ids` (JSON).
AI can reference attachment descriptions when parsing the email for lead extraction.
Supported formats: images (JPEG, PNG, WebP, HEIC), PDFs. Max 10 MB per attachment.

### 10.3 Email threading (conversation history)

Grouped by RFC 2822 headers for proper conversation timelines:
- `message_id_header` — unique message identifier
- `in_reply_to_header` — parent message this is replying to
- `references_header` — full thread ancestry

The Filament relation manager renders a threaded timeline view. Outbound replies set
`In-Reply-To` and `References` headers to maintain the thread on the recipient's side.

### 10.4 Real-time push?

Not needed now. Polling every 2 minutes is acceptable for the target scale. If a tenant
eventually needs instant notifications, Microsoft Graph subscriptions could be added as an
opt-in upgrade — but this is a post-launch consideration.

---

## 11. Platform Inbound Email (No Tenant Account Needed)

For tenants who don't want to connect their own email, we provide a platform-level inbound
address. The tenant forwards or CCs leads to this address, and we process them the same way
as IMAP emails — match known leads or AI-parse for new ones.

### 11.1 Flow

```
Lead sends email to tenant@theirbusiness.com
  → Tenant forwards/redirects to lead@leadgen.test (or CCs it)
  → Mail provider receives it → webhook POST to our server
  → InboundEmailController processes it
  → Same ProcessIncomingEmailJob pipeline (match lead or AI-create)
```

### 11.2 Tenant identification

Two strategies, tried in order:

#### Primary: Plus addressing

```
lead+telhados-lisboa@leadgen.test
     ^^^^^^^^^^^^^^^^
     tenant slug → parse, find Tenant by slug
```

The tenant slug is embedded in the recipient address. Explicit, unambiguous, zero-config.

#### Fallback: Tenant email matching

When a tenant forwards a client's email to `lead@leadgen.test` (no plus addressing), the
**FROM** address of the forwarded message is the **tenant's own email** — not the client's.
We can match this to identify the tenant:

```
1. FROM address = tenant@gmail.com
2. Look up in tenant_email_accounts.email → found → tenant identified
3. Also check User.email (tenant might forward from their login email, not a connected account)
```

This works because email forwarding preserves the forwarder's address in the FROM header.
Gmail, Outlook, and most clients behave this way.

#### Why client sender matching is NOT used for tenant ID

A single email (`client@email.com`) can be a lead for multiple tenants on the platform
(homeowner getting quotes from 3 roofing companies). The FROM address of a direct client
email is ambiguous — we can't know which tenant it belongs to. Client sender matching is
for **lead matching** within an already-identified tenant, not for tenant identification.

#### Resolution order

```
1. Parse recipient for +{slug} → found? → use that tenant
2. Match FROM address to tenant_email_accounts.email → found? → use that tenant
3. Match FROM address to User.email → found? → use that tenant
4. None matched → 550 bounce / silent drop
```

### 11.3 Lead matching (within a tenant)

Once the tenant is identified, we match the sender to a lead **within that tenant's scope**:

```
1. Parse slug from lead+{slug}@leadgen.test → find Tenant
2. Tenant found? → search THAT tenant's leads by from_address
3. Lead found? → store message in lead_email_messages
4. Lead NOT found? → AI parse + create new lead
5. Tenant NOT found? → bounce / silent drop
```

Identical to the IMAP flow — tenant identification just comes from the recipient address
instead of a connected account.

### 11.4 Database additions

**None required.** Plus addressing is parsed at runtime from the recipient. If we later want
per-tenant webhook signing secrets, add `inbound_webhook_secret` to `tenants` — but that's
optional.

### 11.5 Mail provider — webhook-based

We need a provider that:
- Receives email at our domain (`@leadgen.test`)
- Sends an HTTP webhook on each incoming email (real-time, no polling)
- Is cheap or free for MVP scale

| Provider | Free tier | Inbound webhook | Notes |
|---|---|---|---|
| **Mailgun** | 100 emails/day | ✅ `POST /api/webhooks/mailgun` | Most popular, well-documented |
| **Resend** | 100 emails/day | ✅ `POST /api/webhooks/resend` | Modern, good DX, Laravel-friendly |
| **Postmark** | 100 emails/month | ✅ inbound JSON webhook | Smaller free tier |
| **SendGrid** | 100 emails/day | ✅ Inbound Parse | Requires MX record setup |
| **Amazon SES** | 1,000 emails/month | ✅ SNS → Lambda → HTTP | More complex setup |

**Recommendation**: Resend (modern API, Laravel SDK) or Mailgun (proven, battle-tested). Both free tiers cover MVP usage.

### 11.6 Webhook endpoint

```
POST /api/webhooks/inbound-email
  → validates webhook signature (provider-specific)
  → extracts: from, to, cc, subject, body (text + html), attachments
  → resolves tenant via plus addressing (section 11.2)
  → dispatches ProcessIncomingEmailJob with tenant context
```

### 11.7 Reuse existing pipeline

The platform inbound path feeds into the **exact same** `ProcessIncomingEmailJob` used by IMAP.
The only difference is the source:
- IMAP jobs set `tenant_email_account_id` to the connected account
- Webhook jobs set `tenant_email_account_id = null` and add a `source = 'inbound_webhook'` metadata field

Everything else — lead matching, AI parsing, message storage, SMS notifications — is identical.

### 11.8 Outbound: Reply-To and tenant identification on replies

When the platform sends an email on behalf of a tenant (via `SendEmailFromTenantJob` without a
connected account), we set:
- `From: leadgen@leadgen.test` (platform address)
- `Reply-To: tenant@theirbusiness.com` (tenant's actual email, so replies go to them)
- `References:` and `In-Reply-To:` headers to maintain threading

The tenant sees replies directly in their own inbox. If they want those replies captured,
they forward them back to `lead+{slug}@leadgen.test`.

### 11.9 Privacy

Plus addressing shows the tenant slug in the recipient — this is a routing hint, not a
security boundary. A bad actor could guess slugs, but the damage is limited:
- Forwarding an email to `lead+other-tenant@leadgen.test` would only create a lead there
  if the sender matches — and the bad actor has no dashboard access
- Unknown slugs → 550 bounce → sender knows it's invalid
- All inbound emails are logged with resolved tenant for audit

For higher security (regulated industries), HMAC-signed addresses per tenant can be added
post-MVP.

### 11.10 Implementation phases (add-on)

| Phase | What | Est. |
|---|---|---|
| P1 | Mail provider setup (Resend/Mailgun), webhook endpoint, signature validation | 2h |
| P2 | Tenant resolution: plus addressing + tenant email matching (tenant_email_accounts + User emails) | 1.5h |
| P3 | Hook into existing `ProcessIncomingEmailJob` pipeline (with tenant context) | 1h |
| P4 | Filament UI: show platform address per tenant, copy button, forwarding instructions | 1h |
| P5 | Tests | 1h |

**Total**: ~6 hours. Reuses 90% of the existing email processing pipeline.

---

## 12. Google OAuth Send-Only (Zero-Friction Outbound)

For tenants who want to send email from their Gmail account without app passwords or IMAP setup.

### 12.1 The insight

`https://www.googleapis.com/auth/gmail.send` is a **write-only** scope — it allows sending
email via the Gmail API but does NOT grant read access to any existing email content. This
means it should be classified as "sensitive" rather than "restricted" by Google, avoiding
the CASA Tier 2 assessment ($15k-$75k).

**Fallback**: If Google does classify it as restricted, the OAuth access token can still be
used with Gmail's SMTP server via `AUTH XOAUTH2` — no app password needed either way.

### 12.2 Architecture

```
Inbound:  Gmail auto-forwarding → lead+slug@leadgen.test (no OAuth, already built)
Outbound: Google OAuth (gmail.send only) → Gmail API users.messages.send()
          Fallback: Gmail SMTP + OAuth token via XOAUTH2
```

This is separate from the IMAP polling path. A tenant can have:
- **Neither**: uses platform inbound email (section 11)
- **Google OAuth only**: forwarding for inbound, OAuth for outbound
- **IMAP**: full polling-based integration (section 1-9)

### 12.3 Database additions

Minimal — extend `tenant_email_accounts` with OAuth token fields:

```php
// Already exists in the migration: access_token, refresh_token, token_metadata
// These will be used for OAuth tokens instead of (or alongside) app passwords.
// Add a 'connection_type' column to distinguish: 'imap_password', 'google_oauth'
```

Or we can add a new column to avoid confusion:
```php
$table->string('connection_type', 20)->default('imap_password');
// 'imap_password' — traditional app password approach
// 'google_oauth' — OAuth with gmail.send scope
```

### 12.4 OAuth flow

1. Tenant clicks "Sign in with Google" in Filament dashboard
2. Redirect to Google OAuth consent screen requesting: `openid`, `profile`, `email`, `gmail.send`
3. Google redirects back to our callback URL with `?code=...`
4. Exchange code for tokens: `$client->fetchAccessTokenWithAuthCode($code)`
5. Encrypt and store `access_token` + `refresh_token` in `tenant_email_accounts`
6. Store `token_metadata` with `expires_at` for refresh scheduling

### 12.5 Sending via Gmail API

```php
// In SendEmailFromTenantJob, when account.connection_type === 'google_oauth':
$client = new Google\Client();
$client->setAccessToken(Crypt::decryptString($account->access_token));

$gmail = new Google\Service\Gmail($client);

$rawMessage = $this->buildMimeMessage($to, $subject, $body, $threadId);
$encoded = base64_encode($rawMessage);
$encoded = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);

$gmail->users_messages->send('me', new Google\Service\Gmail\Message([
    'raw' => $encoded,
    'threadId' => $threadId, // for conversation threading
]));
```

### 12.6 Token refresh

Google OAuth refresh tokens don't expire unless revoked. The `google/apiclient` library
auto-refreshes access tokens. We hook into the token callback to persist:

```php
$client->setTokenCallback(function ($cacheKey, $accessToken) use ($account) {
    $account->update([
        'access_token' => Crypt::encryptString(json_encode($accessToken)),
        'token_metadata->expires_at' => now()->addSeconds($accessToken['expires_in'] ?? 3600),
    ]);
});
```

### 12.7 Revocation handling

If the user revokes access via their Google Account, the next send attempt gets a 401.
The job catches it → marks the connection as `revoked` → notifies tenant via SMS.

### 12.8 Implementation phases

| Phase | What | Est. |
|---|---|---|
| P1 | Install `google/apiclient`, add `connection_type` column, config | 1h |
| P2 | GoogleOAuthController (redirect + callback), Filament button | 2h |
| P3 | GoogleSendService + update SendEmailFromTenantJob | 1.5h |
| P4 | Token refresh callback + revocation handling | 1h |
| P5 | Tests | 1h |

**Total**: ~6.5 hours.
