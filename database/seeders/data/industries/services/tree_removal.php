<?php

return [
    'key' => 'tree_removal',
    'icon' => '🪓',
    'required_fields' => [
        0 => 'tree_count',
        1 => 'tree_height',
    ],
    'optional_fields' => [
        0 => 'proximity_structures',
        1 => 'stump_removal',
    ],
    'field_definitions' => [
        'tree_count' => [
            'type' => 'select',
            'options' => [
                0 => '1',
                1 => '2_to_3',
                2 => '4_to_6',
                3 => '7_or_more',
            ],
        ],
        'tree_height' => [
            'type' => 'select',
            'options' => [
                0 => 'under_3m',
                1 => '3_to_6m',
                2 => '6_to_15m',
                3 => 'over_15m',
            ],
        ],
        'proximity_structures' => [
            'type' => 'select',
            'options' => [
                0 => 'clear',
                1 => 'near_house',
                2 => 'near_power_lines',
                3 => 'near_road',
                4 => 'difficult_access',
            ],
        ],
        'stump_removal' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
            ],
        ],
    ],
    'conditional_requirements' => [
    ],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'tree_count' => 'Quantas árvores são?',
                'tree_height' => 'Qual a altura aproximada da(s) árvore(s)?',
                'proximity_structures' => 'As árvores estão perto de edifícios, fios elétricos ou estrada?',
                'stump_removal' => 'Quer também a remoção do toco/capas?',
            ],
            'field_options' => [
                'tree_count' => [
                    1 => '1',
                    '2_to_3' => '2-3',
                    '4_to_6' => '4-6',
                    '7_or_more' => '7+',
                ],
                'tree_height' => [
                    'under_3m' => '< 3m',
                    '3_to_6m' => '3-6m',
                    '6_to_15m' => '6-15m',
                    'over_15m' => '> 15m',
                ],
                'proximity_structures' => [
                    'clear' => 'Sem obstáculos',
                    'near_house' => 'Perto de casa',
                    'near_power_lines' => 'Perto de fios',
                    'near_road' => 'Perto da estrada',
                    'difficult_access' => 'Acesso difícil',
                ],
                'stump_removal' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                ],
            ],
            'name' => 'Remoção de Árvores',
            'keywords' => [
                0 => 'árvore',
                1 => 'arvore',
                2 => 'cortar árvore',
                3 => 'cortar arvore',
                4 => 'abate',
                5 => 'podar árvore',
                6 => 'tronco',
                7 => 'raiz',
                8 => 'perigo',
            ],
            'synonyms' => [
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para remoção e poda de árvores. Recolhe informações sobre a árvore e localização. Sê conversador e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
