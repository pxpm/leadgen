# Fixando Form Research — Decision Document

> 28 services analyzed via real Chrome CDP session | 2026-07-10

---

## Executive Summary

### What we should add to shared base fields:

| Priority | Field | Why |
|---|---|---|
| 🔴 HIGH | `property_type` | 100% of Fixando forms ask this. 7/8 plumbing, all construction, all HVAC. We don't. |
| 🔴 HIGH | `water_source` | 3/8 plumbing services ask "Água municipal, poço, ou séptico?" |
| 🟡 MED | `material_supplied` | 7+ services ask "Vai fornecer o material?" |
| 🟡 MED | `affected_fixtures` | 4/8 plumbing services ask which fixtures (multi-select: pia, sanita, chuveiro...) |
| 🟡 MED | `has_project_plan` | Remodelação/Terraços ask "Já tem plano?" |
| 🟢 LOW | `property_occupied` | Pladur/Pintura ask if area is occupied |
| 🟢 LOW | `pipe_material` | 1/8 plumbing (pipe install) asks material: cobre, PVC, PEX... |
| 🟢 LOW | `certification_required` | Eletricidade asks "Certificado?" |

---

## 1. By Our Industry

### 🏠 Construção Civil

**Telhados — Reparação** (9 questions)
| Fixando | We have? |
|---|---|
| Problema? | ✅ `problem_type` |
| Idade telhado? | ✅ `roof_age` |
| Material ATUAL? | ✅ `roof_type` |
| Área (m²)? | 🟡 `roof_size` — but s/m/l, not m² |
| Propriedade? | 🔴 |
| Fotos? | ✅ |
| Cross-sell? | 🔴 |

**Telhados — Substituição** (9 questions — DIFFERENT from Reparação)
| Fixando | vs Reparação |
|---|---|
| **Estruturas no telhado?** (Chaminé, Canos, Clarabóias...) | 🔴 NEW |
| **Material PRETENDIDO?** | 🔴 Different from reparação which asks "material atual" |
| (rest same) | |

**Pintura — Casas** (10 questions)
| Fixando | Status |
|---|---|
| Que espaços? (Multi: Sala, Quartos, WC, Cozinha...) | 🔴 We ask interior/exterior/both, not rooms |
| Quantas divisões? | 🔴 |
| Que superfícies? (Paredes, Teto, Portas...) | 🔴 |
| Área (m²)? | 🟡 |
| Estado paredes? | ✅ `surface_condition` |
| **Vai fornecer tinta?** | 🔴 |
| **Precisa mover móveis?** | 🔴 |
| Fotos? | ✅ |

**Pintura — Exterior** (10 questions)
| Fixando | Status |
|---|---|
| **Altura edifício?** (1/2/3 andares) | 🔴 |
| **Material exterior?** (Madeira, Alumínio, Estuque, Tijolo, Pedra) | 🔴 |
| **Dano exterior?** (Multi: descascar, fissuras, água...) | 🔴 |

**Pladur** (11 questions)
| Fixando | Status |
|---|---|
| Que divisões? (Multi rooms) | 🔴 |
| Material? (Comum, Gesso, Anti-água, Impermeável) | 🔴 |
| Extras? (Insonorização, Impermeabilização, Pintura) | 🔴 |
| **Vai fornecer materiais?** | 🔴 |
| **Área ocupada?** | 🔴 |

**Remodelação — Cozinhas** (8 questions)
| Fixando | Status |
|---|---|
| Tipo trabalho? (Simples, Grandes, Completa) | 🔴 |
| Área cozinha (m²)? | 🔴 |
| **Já tem plano?** (Ideia, Preciso design, Pronto) | 🔴 |
| Componentes? (Multi: Pavimento, Paredes, Armários...) | 🔴 |
| **Vai fornecer materiais?** | 🔴 |

**Construção Civil** (1 question)
| Fixando | Status |
|---|---|
| Tipo serviço? (Mão-de-obra, Consultor, Autorização, Gestor, Empreiteiro) | 🔴 |

---

### ❄️ Climatização

**Instalar AC** (9 questions)
| Fixando | Status |
|---|---|
| Tipo AC? (Split, Inverter, Cassete, Portátil) | ✅ `ac_type` |
| **Já tem equipamento?** | 🔴 |
| Onde controlador? (Cave, Sótão, Armário) | 🔴 |
| Onde condensador? (Térreo, Telhado) | 🔴 |
| Propriedade? | 🔴 |
| Área (m²)? | 🟡 |

**Reparar AC** (6 questions)
| Fixando | Status |
|---|---|
| Problemas? (Não liga, Quente, Congelado, Ventilador) | ✅ `problem_symptom` |
| Elétrico ou gás? | 🟡 `fuel_source` on heating |

**Manutenção AC** (9 questions)
| Fixando | Status |
|---|---|
| Última manutenção? (>2 anos?) | ✅ `last_service` |
| Controlador/Condensador? | 🔴 |

---

### ⚡ Eletricidade

**Problemas Elétricos** (8 questions)
| Fixando | Status |
|---|---|
| Tipo serviço? (Instalar, Reparar, Substituir, Resolver) | ✅ |
| Tipo problema? (Perda, Inconsistente, Dispositivo, Faíscas) | ✅ |
| **Equipamento?** (Multi: Luzes, Interruptores, Tomadas, Quadro, Fios...) | 🔴 |
| **Certificado?** | 🔴 |
| Propriedade? | 🔴 |

---

### 🚿 Canalização (8 sub-services analyzed)

**Reparação de Lavatório e Torneira** (5 questions + shared)
| Fixando | We have? |
|---|---|
| Que problemas? (Entupida, Drenagem lenta, Odor, Vazamentos, Pingar, Pressão baixa, Não funciona) | ✅ `problem_type` |
| Que tipo de pia? (Embarcação, Pedestal, Móvel, Bancada, Parede, Bacia única/dupla) | ✅ `sink_type` |
| Quando começou? (< semana, semanas, mês+) | ✅ `problem_start` |
| Que tipo de torneira? (Manípulo único, Separados) | ✅ `faucet_type` |
| Propriedade? | 🔴 |

**Reparação de Tubos** (5 questions + shared)
| Fixando | We have? |
|---|---|
| Propriedade? | 🔴 |
| Que problemas? (Vazamento, Congelada, Rebentados, Ruidosos, Entupidos, Drenagem lenta, Odor, Inundação) | ✅ `problem_type` |
| Que aparelhos afetados? (Pia, Chuveiro, Sanita, Lava-louça, Máquina lavar, Frigorífico) | 🔴 |
| Água da rede ou esgotos? (Rede, Esgotos, Ambos, Poço) | 🔴 |
| Quando começou? | ✅ `problem_start` |

**Reparação de Banheira/Chuveiro** (5 questions + shared)
| Fixando | We have? |
|---|---|
| Que problemas? (Ralo entupido, Drenagem lenta, Pressão baixa, Vazamentos, Válvula avariada, Vazamento piso inferior, Odor, Controlo temperatura) | ✅ `problem_type` |
| Que tipo? (Chuveiro, Banheira, Combinado, Vapor) | ✅ `bath_type` |
| Quando começou? | ✅ `problem_start` |
| Que tipo de torneira? (Manípulo único, Separados) | ✅ `faucet_type` |
| Propriedade? | 🔴 |

**Canalizador** (general — 3 questions + shared)
| Fixando | We have? |
|---|---|
| Que tipo de trabalho? (Instalar, Reparar, Substituir, Deteção Fugas) | ✅ |
| Qual o problema? (Ruturas, Vazamento, Entupimentos, Ruídos, Odor, Pressão baixa, Temp baixa, Não drena, Não funciona) | ✅ `problem_type` |
| Que parte do sistema? (Pia, Sanita, Chuveiro, Cilindro, Tubos, Lava-louça, Mág. lavar, Mág. loiça) | 🔴 |

**Reparação/Manutenção Canalização Exterior** (4 questions + shared)
| Fixando | We have? |
|---|---|
| Que tipo de trabalho? (Reparar, Inspeção/Manutenção) | ✅ |
| Que componentes? (Mangueira/espigão, Rega, Gás exterior, Água exterior, Esgoto exterior) | 🔴 |
| Quando começou? | ✅ `problem_start` |
| Propriedade? | 🔴 |

**Instalação de Tubos** (5 questions + shared)
| Fixando | We have? |
|---|---|
| Que aparelhos afetados? (Sanita, Pia, Chuveiro, Lava-louça, Frigorífico, Mág. lavar) | 🔴 |
| Material dos tubos? (Cobre, Ferro, Aço, PVC, CPVC, PEX, Recomendado especialista) | 🔴 |
| Ligação água/esgotos? (Municipal, Poço, Tanque séptico, Não sei) | 🔴 |
| Água da rede ou esgotos? (Rede, Esgotos, Ambos, Poço) | 🔴 |
| Propriedade? | 🔴 |

**Reparação de Sanita** (5 questions + shared)
| Fixando | We have? |
|---|---|
| Que problemas? (Entupida, Autoclismo não funciona, Água liberta lentamente, Funciona constantemente, Enche lentamente, Transborda, Vazamento, Odor, Manípulo partido) | ✅ `problem_type` |
| Quando começou? | ✅ `problem_start` |
| Quantas sanitas? (1, 2, 3) | 🔴 |
| Ligação água/esgotos? (Municipal, Poço, Tanque séptico, Não sei) | 🔴 |
| Propriedade? | 🔴 |

**Instalação/Substituição Canalização Exterior** (4 questions + shared)
| Fixando | We have? |
|---|---|
| Que tipo de serviço? (Instalar nova, Remover antiga, Substituir) | ✅ |
| Que equipamento? (Mangueira/espigão, Rega, Gás exterior, Água exterior, Esgoto exterior) | 🔴 |
| Propriedade? | 🔴 |
| **Vai comprar o material?** (Já comprei, Discutir, Especialista compra) | 🔴 |

### 🚿 Plumbing Patterns

| Pattern | Services using it |
|---|---|
| `property_type` | 7/8 services |
| `problem_start` (when did it start?) | 6/8 services |
| `affected_appliances` (multi: pia, sanita, chuveiro...) | 4/8 services |
| `water_source` (municipal/poço/séptico) | 3/8 services |
| `faucet_type` (single/dual handle) | 2/8 services |
| `material_supplied` | 1/8 (exterior install) |
| `pipe_material` (cobre/PVC/PEX...) | 1/8 (pipe install) |
| `toilet_count` (1/2/3) | 1/8 (sanita) |

---

### 🌿 Paisagismo

**Limpeza de Terrenos** (10 questions)
| Fixando | Status |
|---|---|
| Área? (<500m² a 2 hectares) | ✅ `garden_size` |
| Estado? (Árvores+mato, Algum mato, Arbustos) | ✅ `current_state` |
| **Remover lixo?** (Muito, Algum, Só vegetação) | 🔴 |
| Frequência? (Uma vez, Semanal, Quinzenal...) | ✅ `frequency` |

**Sistema de Rega** (9 questions)
| Fixando | Status |
|---|---|
| Manual ou automático? | 🔴 |
| O que regar? (Multi: Relva, Árvores, Flores, Horta...) | 🔴 |
| Área (m²)? | ✅ `area_size` |
| **Vai comprar componentes?** | 🔴 |

---

### 🐀 Controlo de Pragas
⚠️ No wizard. From earlier research: pest type, location, level, property type, pets.

---

## 2. Other Services (Future Industries)

**🪟 Janelas Alumínio** (10 questions): Quantas? Tipo? (Fixa/Guilhotina/Correr/Tradicional/Projetante), Corte térmico?, Já comprou?, Tipo vidro? (Único/Duplo/Triplo), Cor?, Medidas ("Não sei, direi mais tarde"), Propriedade, Fotos

**🪟 Estores** (8 questions): Medir+Instalar/Substituir?, Quantos?+Medidas, Já comprou?, Extras? (Cortinas, Persianas, Controlo remoto), Propriedade, Fotos

**🔧 Handyman** (1 question): Tipo trabalho? (Reparações, Instalação, Manutenção, Montagem) — multi-select

**🪑 Montagem Móveis** (1 question): Tipo mobiliário? (Multi: Cama, Mesa, Cadeira, Estante, Cómoda, Exterior, Baloiços...)

**🗑️ Remoção Lixo** (1 question): Quantidade? (Camioneta, Camião, Camião lixo)

---

## 3. Universal Patterns

### Asked in EVERY Fixando form:
1. **Tipo de propriedade** (Moradia/Apartamento/Escritórios) → 🔴 Add to shared
2. **Área (m² bands)** → 🟡 Standardize from s/m/l to m²
3. **Fotos** → ✅ We have this
4. **Cross-sell** → 🔴 "Tem interesse noutros serviços?"
5. **Postal code + Email** (last step) → ✅ In shared

### Asked in 50%+:
6. **"Vai fornecer o material?"** — Pladur, Estores, Janelas, Pintura, Rega, Remodelação, Canalização Exterior
7. **"Que divisões/equipamentos?"** (multi-select) — Pintura, Pladur, Remodelação, Canalização (4/8 sub-services)
8. **"Já tem equipamento?"** / "Já comprou?" — AC, Janelas, Estores
9. **"Quando começou o problema?"** — 6/8 plumbing services use same 3 time-range options
10. **"Água da rede ou esgotos?"** — 3/8 plumbing services, also relevant for some construção civil

### Multi-select is everywhere:
Fixando uses multi-select for: rooms, surfaces, materials, damage types, equipment, components, furniture types, plants to water. Our `multi_select` FieldType handles this.

---

## 4. Recommended Actions

### 🔴 Now — `property_type` as shared base field

Add to all 6 industry configs:
```php
'property_type' => [
    'type' => 'select',
    'options' => ['apartment', 'house', 'office', 'commercial', 'other'],
]
```
PT labels: Apartamento, Moradia, Escritório, Comercial/Loja, Outro

### � Now — `water_source` for plumbing

Add to plumbing service configs:
```php
'water_source' => [
    'type' => 'select',
    'options' => ['municipal', 'well', 'septic', 'unsure'],
]
```
PT: Água municipal, Água de poço, Tanque séptico, Não tenho a certeza

### 🟡 Next — `material_supplied` for installation services

Add to: painting, pladur, irrigation, windows, roofing (substituição), plumbing (exterior install)
```php
'material_supplied' => [
    'type' => 'select',
    'options' => ['yes_customer', 'yes_with_guidance', 'no_specialist'],
]
```

### 🟡 Next — `affected_fixtures` multi-select for plumbing

4/8 plumbing services ask which fixtures are affected:
```php
'affected_fixtures' => [
    'type' => 'multi_select',
    'options' => ['sink', 'toilet', 'shower', 'bathtub', 'dishwasher', 'washing_machine', 'fridge', 'other'],
]
```

### 🟢 Later (IMPLEMENTED NOW per user request)
- `property_occupied` — ✅ Added to painting + insulation
- `has_project_plan` — ✅ Added to remodeling + terraces
- `pipe_material` — ✅ Added to pipe_replacement (already in leak_repair)
- `certification_required` — ✅ Added to electrical_install + rewiring

---

## 5. Not Implemented (for review)

These Fixando findings were NOT implemented. Reasons noted for each.

| Finding | Reason |
|---|---|
| `property_occupied` on plumbing services | Fixando asks "área ocupada?" only for pladur/pintura — not plumbing. Added to painting + insulation only. |
| `material_supplied` on janelas/estores | These are future industries, not in our current 6. |
| `material_supplied` on plumbing (exterior install) | Our plumbing services don't include "instalação/substituição canalização exterior" as a separate service. `pipe_replacement` covers it partially. |
| `toilet_count` (1/2/3) on sanita repair | Fixando asks "Quantas sanitas?" — we don't have a dedicated "reparação de sanita" service. Covered generically by `bathroom_plumbing` which asks `bathroom_count`. |
| `faucet_type` standardization across plumbing | Already in `leak_repair` + `bathroom_plumbing`. Not needed as shared field since other plumbing services (pipe replacement, drain cleaning) don't care about faucet type. |
| `problem_start` as industry shared field | Added to 5 plumbing services individually. Not added to non-plumbing industries because Fixando only asks "quando começou?" for repair/troubleshooting services, not for installations/remodeling. |
| `water_source` outside plumbing | Fixando only asks about water source for plumbing. Irrigation already has `water_source` with different options (mains/well/borehole/rainwater). |
| `affected_fixtures` outside plumbing | Only plumbing services ask "que aparelhos são afetados?". Not relevant for HVAC, electrical, etc. |
| `cross_sell` ("Tem interesse noutros serviços?") | This is a UX pattern, not a qualification field. Could be added as a post-qualification step. Different from our business model — see UX Note section 6. |
| `property_type` at service level vs shared | Services that already had `property_type` (pipe_replacement, drain_cleaning, roofing, electrical_install, rewiring) keep their service-specific options. Industry shared `property_type` fills the gap for services that didn't have it. |
| Standardize `area_size` from s/m/l to m² bands | Deferred — requires changing all existing area_size fields across services. Non-trivial migration. Worth doing in a separate pass. |
| `pipe_material` expand leak_repair options | leak_repair already has `pipe_material` but with fewer options (copper, PVC, galvanized, PEX, multilayer, not_sure). pipe_replacement now has the expanded list (adds iron, steel, CPVC). Could backport but low priority. |

---

## 6. UX Note

Fixando asks contact info LAST — after all service questions. We ask it FIRST (shared required fields). This is correct for our business model: the lead already found the professional and wants to get in touch. Contact info is the priority, not an afterthought.
