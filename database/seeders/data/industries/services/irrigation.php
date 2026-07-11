<?php

return [
    'key' => 'irrigation',
    'icon' => '💦',
    'required_fields' => [
        0 => 'project_type',
        1 => 'area_size',
    ],
    'optional_fields' => [
        0 => 'water_source',
        1 => 'has_existing',
        2 => 'material_supplied',
    ],
    'field_definitions' => [
        'project_type' => [
            'type' => 'select',
            'options' => [
                0 => 'new_install',
                1 => 'repair',
                2 => 'upgrade',
                3 => 'extension',
                4 => 'other',
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
        'water_source' => [
            'type' => 'select',
            'options' => [
                0 => 'mains',
                1 => 'well',
                2 => 'borehole',
                3 => 'rainwater_tank',
                4 => 'other',
            ],
        ],
        'has_existing' => [
            'type' => 'select',
            'options' => [
                0 => 'none',
                1 => 'manual',
                2 => 'old_system',
                3 => 'partial',
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
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'project_type' => 'Pretende instalar um sistema novo, reparar um existente, ou melhorar?',
                'area_size' => 'Qual é a área a regar? Pequena, média, grande?',
                'water_source' => 'Qual é a fonte de água? Rede, furo, poço, tanque de chuva?',
                'has_existing' => 'Já tem algum sistema de rega? Manual, antigo, parcial?',
                'material_supplied' => 'Vai comprar os componentes ou prefere que o especialista os forneça?',
            ],
            'field_options' => [
                'project_type' => [
                    'new_install' => 'Instalar novo',
                    'repair' => 'Reparar',
                    'upgrade' => 'Melhorar',
                    'extension' => 'Ampliar',
                    'other' => 'Outro',
                ],
                'area_size' => [
                    'small' => 'Pequena',
                    'medium' => 'Média',
                    'large' => 'Grande',
                ],
                'water_source' => [
                    'mains' => 'Rede pública',
                    'well' => 'Poço',
                    'borehole' => 'Furo',
                    'rainwater_tank' => 'Tanque de chuva',
                    'other' => 'Outro',
                ],
                'has_existing' => [
                    'none' => 'Nenhum',
                    'manual' => 'Manual (mangueira)',
                    'old_system' => 'Sistema antigo',
                    'partial' => 'Parcial',
                ],
                'material_supplied' => [
                    'customer_has' => 'Já tenho os componentes',
                    'discuss' => 'Gostaria de discutir',
                    'specialist_provides' => 'Prefiro que o especialista forneça',
                ],
            ],
            'name' => 'Sistemas de Rega',
            'keywords' => [
                0 => 'rega',
                1 => 'irrigação',
                2 => 'irrigacao',
                3 => 'aspersor',
                4 => 'gota a gota',
                5 => 'programador',
                6 => 'automatismo',
            ],
            'synonyms' => [
                'project_type' => [
                    'new_install' => [
                        0 => 'instalar',
                        1 => 'novo',
                        2 => 'meter',
                        3 => 'pôr',
                        4 => 'por',
                        5 => 'primeira vez',
                    ],
                    'repair' => [
                        0 => 'reparar',
                        1 => 'arranjar',
                        2 => 'avariado',
                        3 => 'estragado',
                        4 => 'fuga',
                    ],
                    'upgrade' => [
                        0 => 'melhorar',
                        1 => 'atualizar',
                        2 => 'moderno',
                        3 => 'automático',
                        4 => 'automatico',
                    ],
                    'extension' => [
                        0 => 'aumentar',
                        1 => 'mais',
                        2 => 'estender',
                        3 => 'alargar',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para sistemas de rega. Recolhe informações sobre o espaço e necessidades. Sê conversador e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
