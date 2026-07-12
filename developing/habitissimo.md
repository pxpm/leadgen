# Habitissimo.pt Form Research — Decision Document

> Live Chrome CDP scraping session | 2026-07-12
> Source: https://www.habitissimo.pt/orcamentos
> Method: Playwright connected to user's Chrome (CDP port 9222), clicked "Peça orçamentos", progressed through multi-step modal forms

---

## Executive Summary

Habitissimo uses a **multi-step modal form** (10% → 91% progress bar) for lead intake. Forms have a clear two-tier depth pattern:

1. **Detailed qualification** (4–9 steps): Painting, Roofing, Flooring, Home Renovation, Solar Panels, Insulation
2. **Minimal qualification** (1 step): Electrical, Plumbing, HVAC, Gardening, Cleaning, Pest Control

All forms end with the same contact info step (Name, Email, Phone + Privacy consent + Marketing opt-in).

### What we must add to our app (by priority):

| Priority | Field | Why | Evidence |
|---|---|---|---|
| 🔴 CRITICAL | `property_type` | Asked by 100% of detailed forms | Moradia/Apartamento/Prédio/Comercial/Industrial |
| 🔴 CRITICAL | `work_type` | Construction vs Remodel vs Expansion | "Tipo de obra?" in Telhado, Solar, Renovação |
| 🔴 HIGH | Service sub-type selection | Every form starts here | 3–12 subtypes per service |
| 🔴 HIGH | Area/dimensions (m²) | Asked by Painting, Flooring, Solar, Renovation | Free-text or range m² input |
| 🟡 MEDIUM | Surface/condition assessment | Painting asks "Estado das superfícies?" | Cracks, holes, humidity stains |
| 🟡 MEDIUM | Room-level selection | Painting + Renovation ask "Quais as divisões?" | Multi-select: Sala, Cozinha, Quartos, WC, etc. |
| 🟡 MEDIUM | Property age | Renovação asks "Antiguidade do imóvel?" | Before 1980, 1981–2000, 2001–Present |
| 🟡 MEDIUM | Number of floors | Renovação asks "Número de andares?" | 1, 2, 3+ |
| 🟡 MEDIUM | Material type (specific) | Telhado asks roof material; Pavimentos asks floor type | Ceramic, steel, wood, vinyl, epoxy, etc. |
| 🟢 LOW | Timing/urgency | "Quando pretende começar?" | Standard in all detailed forms |
| 🟢 LOW | Usage purpose | Solar asks "Finalidade de uso?" | Water heating, pool, ambient |

---

## 1. Scraped Services — Complete Form Flows

### 🎨 Pintura (Painting) — 7 qualification steps

The most comprehensive service form on Habitissimo.

| Step | Progress | Question (PT) | Translation | Type | Options |
|---|---|---|---|---|---|
| 1 | 10% | Que tipo de trabalho de Pintura necessita? | What type of painting work? | Radio | Pintar casa, Pintar edifício, Pintar local comercial, Papel de parede, Outros trabalhos de pintura, Pintar muro, Pintar pavilhão industrial, Pintura decorativa |
| 2 | 27% | Área a pintar (m²)? | Area to paint (m²)? | Text | Free number |
| 3 | 36% | Estado das superfícies? | Surface condition? | Radio | Bom estado de conservação, Pequenas fendas, Grandes fendas, Buracos, Manchas de humidade, Outros |
| 4 | 45% | Quais as divisões a pintar? | Which rooms to paint? | Checkboxes | Sala, Cozinha, Quartos, Hall de entrada, Casa de banho, Corredores, Cave, Outros |
| 5 | 55% | Tipo de Habitação? | Property type? | Radio | Moradia unifamiliar, Moradia geminada, Apartamento, Outros |
| 6 | 64% | Tipo de pintura? | Painting type? | Radio | Interior, Exterior, Interior e exterior |
| 7 | 73% | Quando pretende começar o trabalho? | When to start? | Radio | O antes possível, De 1 a 3 meses, Mais de 3 meses, Por enquanto não o penso fazer |
| 8 | 91% | Onde você gostaria de receber propostas? | Contact info | Form | Nome, Email, Telefone + Privacy + Marketing |

---

### 🏠 Remodelação de Casa (Home Renovation) — 9 qualification steps ⭐ MOST DETAILED

The richest form on Habitissimo. Covers nearly every dimension of a renovation project.

| Step | Progress | Question (PT) | Translation | Type | Options |
|---|---|---|---|---|---|
| 1 | 15% | Que tipo de trabalho de Remodelação de casa necessita? | What type of renovation? | Radio | (service sub-types) |
| 2 | 23% | O que pretende remodelar? | What do you want to renovate? | Checkboxes | Chão, Pintura, Janelas, Canalização, Eletricidade, Portas, Azulejos, Aquecimento, Ar condicionado, Insonorização, Outros |
| 3 | 31% | Antiguidade do imóvel? | Property age? | Radio | Antes de 1980, 1981–2000, 2001–Presente, Não sei |
| 4 | 38% | Área bruta de construção (m²)? | Gross construction area? | Radio (ranges) | Entre 35 e 50 m², Entre 51 e 70 m², Entre 71 e 100 m², Mais de 100 m², Não sei |
| 5 | 46% | Quais as divisões a reformar? | Which rooms to renovate? | Checkboxes | Sala, Cozinha, Quartos, Hall de entrada, Casas de banho, Corredores, Cave, Outros |
| 6 | 54% | Número de andares? | Number of floors? | Radio | Um andar, Dois andares, Três ou mais andares, Não sei |
| 7 | 62% | Tipo de Habitação? | Property type? | Radio | Moradia unifamiliar, Moradia geminada, Apartamento, Outros |
| 8 | 69% | Número de divisões? | Number of rooms? | Radio (ranges) | 1 a 3 divisões, 4 a 6 divisões, Mais de 6 divisões, Não sei |
| 9 | 77% | Quando pretende começar o trabalho? | When to start? | Radio | (standard timing options) |
| 10 | ~90% | Contact info | Contact | Form | (standard) |

**Key insight**: This is the blueprint for our renovation qualification. They ask property age (affects materials needed), floor count (affects logistics), and a multi-select for what specifically needs work (floor/paint/windows/plumbing/electrical/doors/tiles/heating/AC/soundproofing). This is far more granular than our current approach.

---

### 🏗️ Instalação de Telhado (Roofing) — 5 qualification steps

| Step | Progress | Question (PT) | Translation | Type | Options |
|---|---|---|---|---|---|
| 1 | 20% | Para qual área você precisa de cotações? | For which area? | Text | Código Postal |
| 2 | 30% | Tipo de obra? | Work type? | Radio | Construção, Remodelação, Ampliação, Outro |
| 3 | 40% | Tipo de imóvel? | Property type? | Radio | Moradia, Prédio, Local comercial, Pavilhão industrial, Outros |
| 4 | 60% | Tipo de telhado? | Roof material? | Radio | Painel sandwich, Telha cerâmica, Cimento, Placas de aço, Outros |
| 5 | 70% | Quando pretende começar? | When to start? | Radio | (standard timing) |
| 6 | 90% | Contact info | Contact | Form | (standard) |

**Key insight**: Roof material type is critical for pricing — sandwich panel vs ceramic tile vs cement have vastly different costs. Also note the "Tipo de obra" distinction (build new vs remodel vs expand) which changes scope dramatically.

---

### 🪵 Pavimentos (Flooring) — 4 qualification steps

| Step | Progress | Question (PT) | Translation | Type | Options |
|---|---|---|---|---|---|
| 1 | 10% | Que tipo de trabalho de Pavimentos necessita? | What type of flooring? | Radio | Pavimento flutuante, Afagar e envernizar madeira, Vinil e linóleo, Tijoleira, Madeira, Resina epóxi, Reparação, Polimento |
| 2 | 33% | Área de aplicação (m²)? | Application area? | Text | Free number |
| 3 | 44% | Tipo de Imóvel? | Property type? | Radio | Moradia, Apartamento, Local comercial, Escritório, Pavilhão industrial, Outros |
| 4 | 67% | Quando pretende começar? | When to start? | Radio | (standard timing) |
| 5 | 89% | Contact info | Contact | Form | (standard) |

**Key insight**: 8 different flooring types — floating, hardwood sanding, vinyl, tile, wood, epoxy resin, repair, polishing. Each implies a completely different contractor specialty and price point.

---

### ☀️ Painéis Solares (Solar Panels) — 5 qualification steps (observed in earlier run)

| Step | Progress | Question (PT) | Translation | Type | Options |
|---|---|---|---|---|---|
| 1 | 10% | Que tipo de trabalho de Painéis solares necessita? | What type of solar work? | Radio | Instalação térmico, Instalação fotovoltaico, Manutenção térmico, Manutenção fotovoltaico, Reparação fotovoltaico, Reparação térmico |
| 2 | 30% | Tipo de imóvel? | Property type? | Radio | Moradia, Apartamento, Edifício residencial, Local comercial, Outros |
| 3 | 40% | Qual o tipo de obra? | Work type? | Radio | Construção, Remodelação, Ampliação, Outros |
| 4 | 50% | Finalidade de uso? | Usage purpose? | Radio | Fornecimento de água quente, Aquecimento de piscina, Aquecimento de ambientes, Outros |
| 5 | 60% | Área da cobertura (telhado) (m²)? | Roof area? | Text | Free number |
| 6 | 70% | Quando pretende começar? | When to start? | Radio | (standard timing) |

**Key insight**: Thermal vs photovoltaic distinction is crucial. Usage purpose changes the system design entirely (water vs pool vs ambient heating).

---

### 🌡️ Isolamento Térmico (Thermal Insulation) — 5 qualification steps (observed in earlier run)

| Step | Progress | Question (PT) | Translation | Type | Options |
|---|---|---|---|---|---|
| 1 | 10% | Que tipo de trabalho de Isolamento térmico necessita? | What type? | Radio | Residencial, Não residencial, Outros |
| 2 | 30% | O que pretende isolar? | What to insulate? | Radio | Telhado, Paredes, Tetos, Chão, Outros |
| 3 | 40% | Tipo de Habitação? | Property type? | Radio | Moradia unifamiliar, Moradia geminada, Apartamento, Outros |
| 4 | 60% | O Isolamento é interno ou externo? | Internal or external? | Radio | Interno, Externo |
| 5 | 70% | Quando pretende começar? | When to start? | Radio | (standard timing) |

**Key insight**: Internal vs external insulation is a fundamental distinction — external (capoto) is much more expensive and involves scaffolding.

---

## 2. Single-Step Services (Minimal Qualification)

These services only ask 1 question (job sub-type) then go to professional matching. All use the pattern: "Que tipo de trabalho de X necessita?"

### ⚡ Instalação Elétrica (Electrical)
| Options |
|---|
| Instalação elétrica integral, Reparação de instalação elétrica, Outros trabalhos de eletricidade |

### 🔧 Canalização (Plumbing)
| Options |
|---|
| Instalação de canalização, Manutenção de tubagens, Outros trabalhos de canalização, Manutenção de autoclismo, Instalação de torneiras, Desentupimentos |

### ❄️ Climatização / Ar Condicionado (HVAC)
| Options |
|---|
| Instalação de ar condicionado, Manutenção de ar condicionado, Outros trabalhos de climatização, Instalação de sistema de exaustão |

### 🌿 Jardinagem (Gardening)
| Options |
|---|
| Manutenção de jardim, Construção de jardim, Outros trabalhos de jardinagem, Poda ou remoção de árvore e arbusto, Plantação de árvore e relva, Instalação de sistema de rega |

### 🧹 Limpeza (Cleaning)
| Options |
|---|
| Limpeza de casa (recorrente), Limpeza de local comercial (recorrente), Limpeza pontual, Limpeza pós-obra, Limpeza de chaminé, Limpeza de piscina, Limpeza de vidro, Limpeza industrial, Limpeza e desinfeção de casa, Limpeza e desinfeção de escritórios, Limpeza e desinfeção de local comercial, Limpeza de condominios |

---

## 3. Cross-Service Pattern Analysis

### Universal questions (appear in ALL detailed forms):

| Question | Appears in |
|---|---|
| Job sub-type selection | 100% of forms (Step 1 always) |
| Property type | Pintura, Telhado, Pavimentos, Solar, Isolamento, Remodelação |
| Timing/urgency | ALL detailed forms |
| Contact info (Nome/Email/Telefone) | ALL forms (final step) |

### Industry-specific questions:

| Question | Appears in |
|---|---|
| Área em m² | Pintura, Pavimentos, Solar, Remodelação |
| Estado/condição atual | Pintura (superfícies) |
| Divisões/compartimentos | Pintura, Remodelação (multi-select checkboxes) |
| Tipo de obra (construção/remodelação/ampliação) | Telhado, Solar, Remodelação |
| Tipo de material | Telhado (5 roof types), Pavimentos (8 floor types) |
| Antiguidade do imóvel | Remodelação (before 1980 / 1981-2000 / 2001+) |
| Número de andares | Remodelação (1 / 2 / 3+) |
| Número de divisões | Remodelação (ranges: 1-3 / 4-6 / 6+) |
| Finalidade de uso | Solar (water/pool/heating) |
| Interno vs externo | Isolamento, Pintura |
| O que pretende isolar | Isolamento (roof/walls/ceiling/floor) |

---

## 4. What Habitissimo Does NOT Ask (Our Opportunities)

| Missing Question | Why It Matters |
|---|---|
| Budget range | No budget qualification — contractors get leads with unknown expectations |
| Photos | No image upload in the form — missed opportunity for visual assessment |
| Previous work history | Has client tried to fix this before? |
| Insurance requirements | Important for construction/remodeling |
| Access constraints | Upper floor without elevator? Narrow streets? |
| Occupancy during work | Living in the property during renovation? |
| Decision timeline | Only "when to start", not "when deciding" |
| Multiple quotes requested | Is the lead shopping around? |
| Emergency vs planned | Plumbing has "desentupimentos" but no explicit emergency flag |

---

## 5. Form UX Patterns Worth Copying

### ✅ Progress bar with percentage
Shows 10% → 91% progression. Each step has a clear title above the options.

### ✅ Step-by-step, one question at a time
Never shows all questions at once. Clean, focused UI. This maps well to our chat widget approach.

### ✅ Service sub-type first, details later
First question ALWAYS gates subsequent questions. Smart filtering for contractor matching.

### ✅ Consistent contact step
Every form ends with Name/Email/Phone + Privacy. Users learn the pattern.

### ✅ Multi-select for rooms/fixtures
Checkboxes for "Quais as divisões?" — better UX than typing room names.

### ✅ Area as ranges for renovation
Remodelação asks area in ranges (35-50, 51-70, 71-100, 100+) instead of free text — easier for homeowners who don't know exact m².

---

## 6. Complete Service Category Map

### Serviços (Services)
| Service | Form Depth | Sub-types |
|---|---|---|
| Pintura | ★★★ 7 steps | 8 |
| Instalação elétrica | ★ 1 step | 3 |
| Canalização | ★ 1 step | 6 |
| Climatização e ar condicionado | ★ 1 step | 4 |
| Painéis solares | ★★★ 5 steps | 6 |
| Isolamento térmico | ★★★ 5 steps | 3 |
| Jardinagem | ★ 1 step | 6 |
| Limpeza | ★ 1 step | 12 |
| Instalação de telhado | ★★★ 5 steps | (postal code entry) |
| Pavimentos | ★★★ 4 steps | 8 |
| Carpintaria e marcenaria | (pending) | — |
| Controlo de pragas | (pending) | — |
| Toldos | (pending) | — |
| Instalação de gás | (pending) | — |
| Gesso cartonado | (pending) | — |
| Impermeabilizações | (pending) | — |
| Caixilharia | (pending) | — |
| Pedreiros | (pending) | — |
| Coberturas e telhados | (pending) | — |

### Remodelações (Renovations)
| Service | Form Depth | Sub-types |
|---|---|---|
| Remodelação casa de banho | (pending) | — |
| Remodelação de cozinha | (pending) | — |
| Remodelação de casa | ★★★★★ 9 steps | 11 areas |
| Aplicação de capoto | (pending) | — |
| Instalação de janela | (pending) | — |

### Construção (Construction)
| Service | Form Depth | Sub-types |
|---|---|---|
| Construção de casa | (pending) | — |
| Arquitetos | (pending) | — |
| Construção de garagem | (pending) | — |
| Construção de piscina | (pending) | — |
| Terraplanagem e demolições | (pending) | — |

### Mudanças (Moving)
| Service | Form Depth | Sub-types |
|---|---|---|
| Mudanças | (pending) | — |
| Mudança local | (pending) | — |

---

## 7. Recommendations for Our App

### Immediate — Add to shared base fields (`app/Enums/FieldType.php`):

1. **`property_type`** — Single select: `moradia_unifamiliar`, `moradia_geminada`, `apartamento`, `predio`, `comercial`, `industrial`, `outro`
2. **`work_type`** — Single select: `construcao`, `remodelacao`, `ampliacao`, `reparacao`, `manutencao`, `outro`
3. **`urgency`** — Single select: `imediato`, `1_3_meses`, `3_mais_meses`, `apenas_pesquisar`

### Service-specific fields to add:

| Service | New Fields |
|---|---|
| Pintura | `surface_condition` (bom/fendas_pequenas/fendas_grandes/buracos/humidade), `rooms_to_paint[]` (multi), `paint_scope` (interior/exterior/ambos) |
| Solar | `solar_type` (termico/fotovoltaico), `usage_purpose` (agua_quente/piscina/ambientes), `roof_area_m2` |
| Telhado | `roof_material` (sandwich/ceramica/cimento/aco), `work_type` (construcao/remodelacao/ampliacao) |
| Pavimentos | `flooring_type` (flutuante/madeira/vinil/tijoleira/epoxi/reparacao/polimento), `floor_area_m2` |
| Remodelação | `renovation_areas[]` (multi: chao/pintura/janelas/canalizacao/eletricidade/portas/azulejos/aquecimento/ac/insonorizacao), `property_age`, `num_floors`, `num_rooms_range`, `gross_area_range` |
| Isolamento | `insulation_target` (telhado/paredes/tetos/chao), `insulation_scope` (interno/externo) |
| Canalização | `plumbing_type` (instalacao/manutencao/autoclismo/torneiras/desentupimentos), `is_emergency` |

### Form UX for our chat widget:

- ✅ Step counter: "Passo 2 de 7"
- ✅ One question per message bubble
- ✅ Service sub-type as the FIRST question
- ✅ Multi-select checkboxes for rooms/areas
- ✅ Free-text m² for precise services (painting, flooring, solar)
- ✅ Ranges for less precise (renovation area)
- ✅ Skip/"Não sei" option for technical questions (like property age)

---

## 8. Technical Notes

- Scraping via Playwright connected to real Chrome (CDP `localhost:9222`)
- Form trigger: Click "Peça orçamentos" button with Playwright's native `.click()`
- Modal detection: `[class*="styles_modal"]` — must find visible one (class `styles_modal__fNQ1X` not the hidden `styles_modal__2ftF0`)
- Step title: `[class*="StepTitle"]` within visible modal
- Some services vary between runs (e.g., Isolamento had 5 steps once, 1 step later) — may be session-based or A/B testing
- Services redirecting to "À procura de profissionais..." after 1 step = their "quick match" flow
- Portuguese postal code used: `1000-001` (Lisbon)

---

> **Next Steps**: Cross-reference with `industry_questions_research.md` (Fixando data). Identify the union of questions both platforms ask. Prioritize which fields go into shared base vs industry-specific configs. Build the `field_patterns.php` config entries for each industry.
