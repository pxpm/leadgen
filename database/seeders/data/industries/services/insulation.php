<?php

return [
    'key' => 'insulation',
    'icon' => '🌡️',
    'required_fields' => [
        0 => 'insulation_type',
    ],
    'optional_fields' => [
        0 => 'area_size',
        1 => 'current_insulation',
        2 => 'material_supplied',
        3 => 'property_occupied',
    ],
    'field_definitions' => [
        'insulation_type' => [
            'type' => 'select',
            'options' => [
                0 => 'capoto',
                1 => 'roof_insulation',
                2 => 'acoustic',
                3 => 'thermal_correction',
                4 => 'other',
            ],
        ],
        'building_type' => [
            'type' => 'select',
            'options' => [
                0 => 'detached',
                1 => 'semi_detached',
                2 => 'apartment_building',
                3 => 'commercial',
                4 => 'industrial',
                5 => 'other',
            ],
            'required' => true,
            'when' => [
                'property_type' => [
                    0 => 'house',
                    1 => 'commercial',
                    2 => 'industrial',
                ],
            ],
        ],
        'area_size' => [
            'type' => 'select',
            'options' => [
                0 => 'small',
                1 => 'medium',
                2 => 'large',
                3 => 'not_sure',
            ],
        ],
        'current_insulation' => [
            'type' => 'select',
            'options' => [
                0 => 'none',
                1 => 'old_damaged',
                2 => 'partial',
                3 => 'unknown',
            ],
        ],
        'material_supplied' => [
            'type' => 'select',
            'options' => [
                0 => 'customer_has',
                1 => 'discuss',
                2 => 'specialist_provides',
            ],
        ],
        'property_occupied' => [
            'type' => 'select',
            'options' => [
                0 => 'yes_occupied',
                1 => 'yes_furnished',
                2 => 'vacant',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'insulation_type' => 'Que tipo de isolamento precisa? Capoto (fachada), isolamento de telhado, isolamento acústico?',
                'building_type' => 'E especificamente, que tipo de construção? Moradia isolada, geminada, prédio?',
                'area_size' => 'Qual é o tamanho aproximado da área a isolar?',
                'current_insulation' => 'Atualmente tem algum tipo de isolamento instalado?',
                'material_supplied' => 'Vai fornecer os materiais ou prefere que o especialista os forneça?',
                'property_occupied' => 'A propriedade está ocupada? Há móveis para proteger?',
            ],
            'field_options' => [
                'insulation_type' => [
                    'capoto' => 'Capoto/ETICS',
                    'roof_insulation' => 'Isolamento de telhado',
                    'acoustic' => 'Isolamento acústico',
                    'thermal_correction' => 'Correção térmica',
                    'other' => 'Outro',
                ],
                'building_type' => [
                    'detached' => 'Moradia isolada',
                    'semi_detached' => 'Moradia geminada',
                    'apartment_building' => 'Prédio de apartamentos',
                    'commercial' => 'Espaço comercial',
                    'industrial' => 'Pavilhão industrial',
                    'other' => 'Outro',
                ],
                'area_size' => [
                    'small' => 'Pequena (<50m²)',
                    'medium' => 'Média (50-200m²)',
                    'large' => 'Grande (>200m²)',
                    'not_sure' => 'Não sei',
                ],
                'current_insulation' => [
                    'none' => 'Nenhum',
                    'old_damaged' => 'Antigo/degradado',
                    'partial' => 'Parcial',
                    'unknown' => 'Não sei',
                ],                'material_supplied' => [
                    'customer_has' => 'Já tenho os materiais',
                    'discuss' => 'Gostaria de discutir',
                    'specialist_provides' => 'Prefiro que o especialista forneça',
                ],
                'property_occupied' => [
                    'yes_occupied' => 'Sim, está ocupada e mobilada',
                    'yes_furnished' => 'Sim, mas posso proteger',
                    'vacant' => 'Está vazia',
                ],            ],
            'name' => 'Isolamento/Capoto',
            'keywords' => [
                0 => 'isolamento',
                1 => 'capoto',
                2 => 'isolar',
                3 => 'térmico',
                4 => 'termico',
                5 => 'acústico',
                6 => 'acustico',
            ],
            'synonyms' => [
                'insulation_type' => [
                    'capoto' => [
                        0 => 'capoto',
                        1 => 'etics',
                        2 => 'isolamento exterior',
                        3 => 'fachada ventilada',
                        4 => 'reboco isolante',
                    ],
                    'roof_insulation' => [
                        0 => 'telhado',
                        1 => 'cobertura',
                        2 => 'sótão',
                        3 => 'sotao',
                        4 => 'desvão',
                        5 => 'desvao',
                        6 => 'teto',
                    ],
                    'acoustic' => [
                        0 => 'acústico',
                        1 => 'acustico',
                        2 => 'som',
                        3 => 'ruído',
                        4 => 'ruido',
                        5 => 'barulho',
                    ],
                    'thermal_correction' => [
                        0 => 'térmico',
                        1 => 'termico',
                        2 => 'frio',
                        3 => 'calor',
                        4 => 'pontes térmicas',
                        5 => 'condensação',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de isolamento térmico e acústico. O teu trabalho é recolher informações sobre o projeto de isolamento. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. Nunca dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON. Valores:
- insulation_type: capoto, roof_insulation, acoustic, thermal_correction, other
- building_type: house, apartment_building, commercial, industrial, other
- area_size: small, medium, large, not_sure
- current_insulation: none, old_damaged, partial, unknown',
            ],
        ],
    ],
];
