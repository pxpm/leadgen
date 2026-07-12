# Follow-Up Communication System — Implementation Plan

> AI-powered email composition for contractors to communicate with leads.
> Goal: save time, improve communication quality, reduce lead abandonment.

---

## 1. Vision

Contractors struggle with client communication — they don't have time or writing skills for professional emails. We'll give them a **chip-based interface** where they pick a scenario + reasons/details, and AI composes a polished email in seconds. One click to review, edit, and send.

### Core principles:
- **30 seconds from decision to sent email** — no typing required
- **Professional tone always** — AI handles the wording
- **Human review always** — contractor sees and can edit before sending
- **All communications tracked** — full history per lead

---

## 2. Scenarios (MVP)

### Scenario A: Decline Lead ("Rejeitar Lead")
The contractor can't or won't take this job. Instead of ghosting the lead, they send a polite decline.

**Reasons (chip-select, multi):**
| Key | Label (PT) | Example phrasing |
|---|---|---|
| `no_availability` | Sem disponibilidade de agenda | "Unfortunately my schedule is fully committed..." |
| `out_of_area` | Fora da área de serviço | "I don't cover your location, but here's what I suggest..." |
| `job_too_small` | Trabalho demasiado pequeno | "This project is below the minimum scope I typically take on..." |
| `job_too_big` | Trabalho demasiado grande | "This project exceeds what I can handle alone..." |
| `not_specialty` | Não é a minha especialidade | "This isn't my core expertise, but I can recommend..." |
| `already_booked` | Já tenho outro projeto | "I'm currently committed to another project until..." |
| `budget_mismatch` | Orçamento não compatível | "The budget doesn't align with the scope of work needed..." |
| `other` | Outro motivo | Free-text reason |

**AI prompt direction:** "You are a professional contractor. Politely decline this lead. Be respectful, thank them for reaching out. If applicable, suggest alternatives (another contractor type, a different time, etc.). Keep it warm — they might come back later."

### Scenario B: Request More Info ("Pedir Mais Informações")
The lead is missing critical details. Instead of a vague "send me more info," the contractor picks exactly what they need.

**Info requests (chip-select, multi):**
| Key | Label (PT) | Example phrasing |
|---|---|---|
| `photos` | Fotos do local/situação | "Could you send a few photos of the area?" |
| `exact_address` | Morada exata | "Could you confirm the exact address?" |
| `dimensions` | Medidas/área exata | "Do you know the approximate dimensions in m²?" |
| `budget` | Expectativa de orçamento | "Do you have a budget range in mind?" |
| `timeline` | Prazo desejado | "When would you like the work to start?" |
| `access_details` | Detalhes de acesso | "Is there easy access? Any stairs, narrow doors?" |
| `previous_work` | Trabalhos anteriores | "Has any work been done on this before?" |
| `material_preference` | Preferência de materiais | "Do you have a preference for materials or brands?" |
| `other` | Outra informação | Free-text custom request |

**AI prompt direction:** "You are a contractor following up on a lead. Politely request the missing information. Be specific about what you need and why it helps provide a better quote. Keep it friendly and collaborative."

### Scenario C: Send Quote Follow-up ("Acompanhar Orçamento")
The contractor sent a quote but hasn't heard back.

**Options (chip-select, single):**
| Key | Label (PT) |
|---|---|
| `first_followup` | Primeiro acompanhamento (3 dias) |
| `second_followup` | Segundo acompanhamento (7 dias) |
| `final_followup` | Último contacto (14 dias) |

**AI prompt direction:** "You are a contractor following up on a quote. Tone depends on follow-up number: first is gentle, second is slightly more direct, third is final check-in. Never be pushy — respect their decision."

### Scenario D: General Check-in ("Contacto Geral")
Free-form communication. Contractor types a brief note, AI polishes it.

**Inputs:**
- Topic (free text or chip: `scheduling`, `update`, `thank_you`, `other`)
- Brief notes (optional free text)
- AI polishes into professional email

---

## 3. Data Model

### New Model: `FollowUpAction`

Tracks every communication action taken on a lead.

```php
// database/migrations/xxxx_create_follow_up_actions_table.php
Schema::create('follow_up_actions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
    $table->string('scenario');        // 'decline', 'request_info', 'quote_followup', 'general'
    $table->json('selected_reasons');   // ['no_availability', 'out_of_area']
    $table->json('selected_fields');    // ['photos', 'exact_address'] (for request_info)
    $table->text('free_text')->nullable();       // contractor's custom notes
    $table->text('generated_email')->nullable(); // AI-generated email body
    $table->text('final_email')->nullable();     // what was actually sent (after edits)
    $table->string('status')->default('draft');  // draft, sent, discarded
    $table->timestamp('sent_at')->nullable();
    $table->timestamps();
});
```

### New Enum: `FollowUpScenario`

```php
// app/Enums/FollowUpScenario.php
enum FollowUpScenario: string
{
    case Decline = 'decline';
    case RequestInfo = 'request_info';
    case QuoteFollowUp = 'quote_followup';
    case General = 'general';
}
```

### Reuse: `Notification` model

Already exists and tracks sent communications. The `FollowUpAction` generates a `Notification` record when sent.

---

## 4. AI Integration

### Service: `EmailComposer`

```php
// app/Services/EmailComposer.php
class EmailComposer
{
    public function compose(
        Lead $lead,
        FollowUpScenario $scenario,
        array $reasons,       // selected reasons/chips
        ?string $freeText,    // contractor's custom notes
    ): string;
}
```

**How it works:**
1. Build a structured prompt with:
   - Contractor's business name (from tenant)
   - Lead's name and collected info (from lead fields)
   - Scenario context + selected reasons
   - Tone instructions
2. Call DeepSeek via `laravel/ai` SDK
3. Return composed email body
4. Contractor reviews, edits if needed, clicks send

**Prompt structure:**
```
System: You are a professional email composer for a construction/renovation
contractor in Portugal. Write in Portuguese (pt-PT). Be professional, warm,
and concise. Never invent information not provided.

Context:
- Contractor: [tenant.name]
- Client: [lead.contact_name]
- Project: [lead.service_type] — [key details from lead fields]
- Scenario: [decline / request_info / quote_followup]
- Reasons: [selected chips]
- Contractor notes: [free_text or empty]

Task: Write a complete email body (no subject line). Use appropriate greeting
and signature. Match the scenario's tone.
```

### Fallback: Template-based

If AI fails, use a pre-written template with placeholders filled in. Templates live in `config/follow_up_templates.php` or `resources/lang/pt/follow_up.php`.

---

## 5. UI Flow

### In Filament Admin Panel

```
LeadResource
  ├── View Lead (existing infolist + conversation history)
  ├── [NEW] "Ações de Seguimento" section
  │     ├── Button: "Rejeitar Lead" → opens modal
  │     ├── Button: "Pedir Informações" → opens modal
  │     ├── Button: "Acompanhar Orçamento" → opens modal
  │     └── Button: "Contacto Geral" → opens modal
  └── [NEW] Timeline of follow-up actions
```

### Modal Flow (for each scenario):

```
┌─────────────────────────────────────────┐
│  Rejeitar Lead                      [×] │
│                                         │
│  Motivo(s):                             │
│  [Sem disponibilidade] [Fora da área]   │
│  [Trabalho pequeno] [Não é especial.]   │
│  [Orçamento] [Outro: ___________]       │
│                                         │
│  Notas adicionais (opcional):           │
│  [______________________________]       │
│                                         │
│  [Gerar Email com IA]                   │
│  ───────────────────────────────        │
│  Preview do email:                      │
│  ┌─────────────────────────────────┐    │
│  │ Olá João,                       │    │
│  │                                 │    │
│  │ Muito obrigado pelo contacto... │    │
│  │                                 │    │
│  │ [editável]                      │    │
│  └─────────────────────────────────┘    │
│                                         │
│  [Editar]  [Enviar Email]  [Descartar] │
└─────────────────────────────────────────┘
```

### Widget/Livewire Component

The modal is a Filament action that opens a custom Livewire component:

```php
// app/Livewire/FollowUpComposer.php
class FollowUpComposer extends Component
{
    public Lead $lead;
    public string $scenario;
    public array $selectedReasons = [];
    public array $selectedFields = [];
    public string $freeText = '';
    public string $generatedEmail = '';
    public string $editedEmail = '';
    public bool $isGenerating = false;
    public bool $isSending = false;

    public function generateEmail(): void
    public function sendEmail(): void
    public function discard(): void
}
```

---

## 6. API Endpoints

For future widget integration (contractor can act on leads from mobile/email):

```
POST   /api/tenant/leads/{lead}/follow-up/generate   — generate email
POST   /api/tenant/leads/{lead}/follow-up/send       — send email
GET    /api/tenant/leads/{lead}/follow-up/history     — list actions
```

MVP: Filament-only. API comes later.

---

## 7. Email Sending

### Transport

Uses Laravel's mail system (already configured with `bentonow.com` via `MAIL_MAILER`).

### Sender identity

- **From:** tenant's configured email (from `notification_config`)
- **Reply-To:** tenant's configured email
- **To:** lead's email (`lead.fields.where('field_key', 'email')`)

### Class: `FollowUpMail`

```php
// app/Mail/FollowUpMail.php
class FollowUpMail extends Mailable
{
    public function __construct(
        public Lead $lead,
        public string $body,
        public ?string $subject,
    ) {}
}
```

### Subject lines per scenario:

| Scenario | Subject (PT) |
|---|---|
| Decline | "Sobre o seu pedido de orçamento — [tenant.name]" |
| Request Info | "Informações adicionais para o seu orçamento" |
| Quote Follow-up | "Acompanhamento do seu orçamento" |
| General | "Contacto — [tenant.name]" |

---

## 8. Implementation Phases

### Phase 1 — Core Engine (this session)

1. Create migration for `follow_up_actions` table
2. Create `FollowUpAction` model
3. Create `FollowUpScenario` enum
4. Create `EmailComposer` service with AI integration
5. Create `FollowUpMail` mailable
6. Create `FollowUpComposer` Livewire component
7. Add actions to `LeadResource` Filament page
8. Create `config/follow_up.php` with scenario definitions
9. Write tests

### Phase 2 — Polish (next session)

1. Email history timeline on lead view
2. Templates for each scenario (AI fallback)
3. Subject line customization
4. Preview email before sending (render in modal)
5. Track open rates (if supported by bentonow)

### Phase 3 — Advanced

1. SMS follow-ups (reuse scenarios, different channel)
2. Scheduled follow-ups ("remind me in 3 days")
3. Follow-up sequences (automated drip)
4. Widget integration (contractor sees lead + can act from phone)

---

## 9. Configuration

### `config/follow_up.php`

```php
return [
    'scenarios' => [
        'decline' => [
            'label' => 'Rejeitar Lead',
            'icon' => 'heroicon-o-x-circle',
            'reasons' => [
                'no_availability' => 'Sem disponibilidade de agenda',
                'out_of_area' => 'Fora da área de serviço',
                'job_too_small' => 'Trabalho demasiado pequeno',
                'job_too_big' => 'Trabalho demasiado grande',
                'not_specialty' => 'Não é a minha especialidade',
                'already_booked' => 'Já tenho outro projeto',
                'budget_mismatch' => 'Orçamento não compatível',
                'other' => 'Outro motivo',
            ],
            'subject' => 'Sobre o seu pedido de orçamento',
            'ai_prompt' => 'You are a professional contractor in Portugal...',
        ],
        'request_info' => [
            'label' => 'Pedir Informações',
            'icon' => 'heroicon-o-question-mark-circle',
            'fields' => [
                'photos' => 'Fotos do local',
                'exact_address' => 'Morada exata',
                'dimensions' => 'Medidas/área',
                'budget' => 'Expectativa de orçamento',
                'timeline' => 'Prazo desejado',
                'access_details' => 'Detalhes de acesso',
                'previous_work' => 'Trabalhos anteriores',
                'material_preference' => 'Preferência de materiais',
                'other' => 'Outra informação',
            ],
            'subject' => 'Informações adicionais para o seu orçamento',
            'ai_prompt' => 'You are a contractor following up...',
        ],
        'quote_followup' => [
            'label' => 'Acompanhar Orçamento',
            'icon' => 'heroicon-o-clock',
            'stages' => [
                'first_followup' => 'Primeiro acompanhamento (3 dias)',
                'second_followup' => 'Segundo acompanhamento (7 dias)',
                'final_followup' => 'Último contacto (14 dias)',
            ],
            'subject' => 'Acompanhamento do seu orçamento',
            'ai_prompt' => 'You are a contractor following up...',
        ],
        'general' => [
            'label' => 'Contacto Geral',
            'icon' => 'heroicon-o-chat-bubble-left',
            'subject' => null, // AI generates subject too
            'ai_prompt' => 'You are a contractor reaching out...',
        ],
    ],

    'ai' => [
        'provider' => 'deepseek',
        'model' => 'deepseek-chat',
        'max_tokens' => 500,
        'temperature' => 0.7,
    ],
];
```

---

## 10. Files to Create/Modify

### New files:
| File | Purpose |
|---|---|
| `app/Enums/FollowUpScenario.php` | Scenario enum |
| `app/Models/FollowUpAction.php` | Action model |
| `app/Services/EmailComposer.php` | AI email generation |
| `app/Mail/FollowUpMail.php` | Mailable class |
| `app/Livewire/FollowUpComposer.php` | Modal UI component |
| `config/follow_up.php` | Scenario definitions |
| `database/migrations/xxxx_create_follow_up_actions.php` | Migration |
| `tests/Unit/EmailComposerTest.php` | Service tests |
| `tests/Feature/FollowUpActionTest.php` | Feature tests |

### Modified files:
| File | Change |
|---|---|
| `app/Filament/Resources/LeadResource.php` | Add follow-up action buttons |
| `app/Filament/Resources/LeadResource/Pages/ViewLead.php` | Add timeline section |

---

> **Ready to implement.** Start with Phase 1: migration, model, enum, composer service, Livewire component, Filament integration. Test each scenario with real AI output.
---

## 10. Learning System � Few-Shot Style Adaptation ? IMPLEMENTED

### Problem
AI generates inconsistent emails for the same scenario. Each contractor has their own communication style, and the AI should learn it over time.

### Solution: Few-shot learning via `email_learning_examples` table

**How it works:**
1. When a contractor sends an email, we record both the AI-generated version and what was actually sent
2. If the contractor edited the email before sending (`generated ? final`), we store it as a learning example
3. Next time the same contractor uses the same scenario + reasons, we fetch the last 2 edited examples
4. These examples are injected into the AI prompt: "Here are examples of how this contractor writes: [example 1], [example 2]. Use a similar style."

**Architecture:**
- `email_learning_examples` table: `tenant_id`, `scenario`, `reasons_hash` (MD5 for fast lookup), `generated_body`, `sent_body`, `was_edited`
- `EmailLearningExample` model: `recordFromAction()`, `findSimilar()`
- `EmailComposer` automatically fetches examples before generating
- `FollowUpComposer` records examples on send

**Why this approach (not vector DB or fine-tuning):**
- Zero additional infrastructure � uses existing MySQL
- Immediate feedback loop � first edit improves the next generation
- Tenant-scoped � each contractor's style stays private to them
- Cost-free � no embedding API calls, no model training
- Simple � 1 table, 1 model, ~50 lines of code

**Future enhancement path:**
- If examples grow large (>1000), add embedding-based similarity search
- If enough data, offer to fine-tune a small model per tenant

---

> **Phase 1 complete.** 140 tests passing. Migrations run. Ready for UI testing.
