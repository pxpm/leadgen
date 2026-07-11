<?php

return [
    'key' => 'paving_decking',
    'icon' => '🧱',
    'required_fields' => [
        0 => 'project_type',
        1 => 'area_size',
    ],
    'optional_fields' => [
        0 => 'material_preference',
        1 => 'has_drainage',
    ],
    'field_definitions' => [
        'project_type' => [
            'type' => 'select',
            'options' => [
                0 => 'paving',
                1 => 'deck',
                2 => 'wall',
                3 => 'path',
                4 => 'driveway',
                5 => 'patio',
                6 => 'other',
            ],
        ],
        'area_size' => [
            'type' => 'select',
            'options' => [
                0 => 'small',
                1 => 'medium',
                2 => 'large',
            ],
        ],
        'material_preference' => [
            'type' => 'select',
            'options' => [
                0 => 'stone',
                1 => 'wood',
                2 => 'composite',
                3 => 'concrete',
                4 => 'ceramic',
                5 => 'gravel',
                6 => 'not_sure',
            ],
        ],
        'has_drainage' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'needs_work',
                3 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'project_type' => 'O que pretende construir? Pavimento, deck de madeira, muro, caminho?',
                'area_size' => 'Qual a área aproximada?',
                'material_preference' => 'Tem preferência de material? Pedra, madeira, compósito, betão?',
                'has_drainage' => 'O terreno tem boa drenagem ou precisa de ser resolvida?',
            ],
            'field_options' => [
                'project_type' => [
                    'paving' => 'Pavimento',
                    'deck' => 'Deck',
                    'wall' => 'Muro',
                    'path' => 'Caminho',
                    'driveway' => 'Entrada',
                    'patio' => 'Pátio',
                    'other' => 'Outro',
                ],
                'area_size' => [
                    'small' => 'Pequena (< 30m²)',
                    'medium' => 'Média (30-100m²)',
                    'large' => 'Grande (> 100m²)',
                ],
                'material_preference' => [
                    'stone' => 'Pedra',
                    'wood' => 'Madeira',
                    'composite' => 'Compósito',
                    'concrete' => 'Betão',
                    'ceramic' => 'Cerâmica',
                    'gravel' => 'Gravilha',
                    'not_sure' => 'Não sei/Aconselhem-me',
                ],
                'has_drainage' => [
                    'yes' => 'Sim, boa',
                    'no' => 'Não',
                    'needs_work' => 'Precisa de obras',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Pavimentos e Decks',
            'keywords' => [
                0 => 'pavimento',
                1 => 'deck',
                2 => 'calcada',
                3 => 'calçada',
                4 => 'deck de madeira',
                5 => 'muro',
                6 => 'passeio',
                7 => 'pedra',
            ],
            'synonyms' => [
                'project_type' => [
                    'paving' => [
                        0 => 'pavimento',
                        1 => 'pavimentar',
                        2 => 'calçada',
                        3 => 'calcada',
                        4 => 'laje',
                        5 => 'lajes',
                    ],
                    'deck' => [
                        0 => 'deck',
                        1 => 'madeira',
                        2 => 'estrado',
                        3 => 'plataforma',
                    ],
                    'wall' => [
                        0 => 'muro',
                        1 => 'parede',
                        2 => 'suporte',
                        3 => 'contenção',
                    ],
                    'path' => [
                        0 => 'caminho',
                        1 => 'passeio',
                        2 => 'acesso',
                        3 => 'percurso',
                    ],
                    'driveway' => [
                        0 => 'entrada',
                        1 => 'garagem',
                        2 => 'estacionamento',
                        3 => 'acesso carros',
                    ],
                    'patio' => [
                        0 => 'pátio',
                        1 => 'patio',
                        2 => 'terraço',
                        3 => 'terraco',
                        4 => 'lazer',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para pavimentos e decks exteriores. Recolhe informações sobre o projeto e preferências de material. Sê conversador e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
