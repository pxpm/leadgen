<?php

return [
    'key' => 'terraces',
    'icon' => '🏗️',
    'required_fields' => [
        0 => 'terrace_type',
        1 => 'problem_type',
    ],
    'optional_fields' => [
        0 => 'area_size',
        1 => 'current_surface',
        2 => 'has_project_plan',
    ],
    'field_definitions' => [
        'terrace_type' => [
            'type' => 'select',
            'options' => [
                0 => 'flat_roof',
                1 => 'balcony',
                2 => 'rooftop',
                3 => 'ground_floor',
                4 => 'other',
            ],
        ],
        'problem_type' => [
            'type' => 'select',
            'options' => [
                0 => 'leak',
                1 => 'crack',
                2 => 'wear',
                3 => 'preventive',
                4 => 'other',
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
        'current_surface' => [
            'type' => 'select',
            'options' => [
                0 => 'tile',
                1 => 'concrete',
                2 => 'membrane',
                3 => 'gravel',
                4 => 'wood',
                5 => 'other',
            ],
        ],
        'has_project_plan' => [
            'type' => 'select',
            'options' => [
                0 => 'idea_only',
                1 => 'need_design',
                2 => 'plans_ready',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'terrace_type' => 'Que tipo de terraço é? Cobertura plana, varanda, rooftop ou rés-do-chão?',
                'problem_type' => 'Qual é o problema? Tem infiltrações, fissuras, desgaste ou é manutenção preventiva?',
                'area_size' => 'Qual é o tamanho aproximado do terraço?',
                'current_surface' => 'Que tipo de piso/revestimento tem atualmente o terraço?',
                'has_project_plan' => 'Já tem um plano ou projeto definido para o terraço?',
            ],
            'field_options' => [
                'terrace_type' => [
                    'flat_roof' => 'Cobertura plana',
                    'balcony' => 'Varanda',
                    'rooftop' => 'Rooftop',
                    'ground_floor' => 'Rés-do-chão/Pátio',
                    'other' => 'Outro',
                ],
                'problem_type' => [
                    'leak' => 'Infiltração',
                    'crack' => 'Fissuras',
                    'wear' => 'Desgaste',
                    'preventive' => 'Preventivo',
                    'other' => 'Outro',
                ],
                'area_size' => [
                    'small' => 'Pequeno (<20m²)',
                    'medium' => 'Médio (20-100m²)',
                    'large' => 'Grande (>100m²)',
                    'not_sure' => 'Não sei',
                ],
                'current_surface' => [
                    'tile' => 'Cerâmica/Ladrilho',
                    'concrete' => 'Betão',
                    'membrane' => 'Tela impermeável',
                    'gravel' => 'Godô/Brita',
                    'wood' => 'Madeira/Deck',
                    'other' => 'Outro',
                ],
                'has_project_plan' => [
                    'idea_only' => 'Tenho ideia do que quero',
                    'need_design' => 'Preciso de ajuda com o design',
                    'plans_ready' => 'Já tenho projeto pronto',
                ],
            ],
            'name' => 'Terraços',
            'keywords' => [
                0 => 'terraço',
                1 => 'terraco',
                2 => 'varanda',
                3 => 'varandas',
                4 => 'marquise',
            ],
            'synonyms' => [
                'terrace_type' => [
                    'flat_roof' => [
                        0 => 'cobertura plana',
                        1 => 'telhado plano',
                        2 => 'cobertura',
                        3 => 'placa',
                    ],
                    'balcony' => [
                        0 => 'varanda',
                        1 => 'sacada',
                        2 => 'marquise',
                    ],
                    'rooftop' => [
                        0 => ' rooftop',
                        1 => 'cobertura',
                        2 => 'último piso',
                        3 => 'ultimo piso',
                        4 => 'topo',
                    ],
                    'ground_floor' => [
                        0 => 'rés-do-chão',
                        1 => 'res do chao',
                        2 => 'pátio',
                        3 => 'patio',
                        4 => 'logradouro',
                    ],
                ],
                'problem_type' => [
                    'leak' => [
                        0 => 'infiltração',
                        1 => 'infiltracao',
                        2 => 'goteira',
                        3 => 'água',
                        4 => 'agua',
                        5 => 'humidade',
                        6 => 'umidade',
                    ],
                    'crack' => [
                        0 => 'fissura',
                        1 => 'fenda',
                        2 => 'rachado',
                        3 => 'partido',
                        4 => 'quebrado',
                    ],
                    'wear' => [
                        0 => 'desgaste',
                        1 => 'gasto',
                        2 => 'velho',
                        3 => 'degradado',
                        4 => 'danificado',
                    ],
                    'preventive' => [
                        0 => 'prevenir',
                        1 => 'prevenção',
                        2 => 'prevencao',
                        3 => 'manutenção',
                        4 => 'manutencao',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de terraços. O teu trabalho é recolher informações sobre o terraço para que um técnico possa preparar um orçamento. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. Nunca dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON. Valores:
- terrace_type: flat_roof, balcony, rooftop, ground_floor, other
- problem_type: leak, crack, wear, preventive, other
- area_size: small, medium, large, not_sure
- current_surface: tile, concrete, membrane, gravel, wood, other',
            ],
        ],
    ],
];
