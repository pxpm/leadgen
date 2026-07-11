<?php

return [
    'key' => 'rodent_control',
    'icon' => '🐀',
    'required_fields' => [
        0 => 'sighting_location',
        1 => 'property_type',
    ],
    'optional_fields' => [
        0 => 'infestation_level',
        1 => 'has_pets',
    ],
    'field_definitions' => [
        'sighting_location' => [
            'type' => 'select',
            'options' => [
                0 => 'kitchen',
                1 => 'attic',
                2 => 'basement',
                3 => 'garden',
                4 => 'garage',
                5 => 'walls',
                6 => 'multiple',
                7 => 'other',
            ],
        ],
        'property_type' => [
            'type' => 'select',
            'options' => [
                0 => 'apartment',
                1 => 'house',
                2 => 'restaurant',
                3 => 'warehouse',
                4 => 'office',
                5 => 'other',
            ],
        ],
        'infestation_level' => [
            'type' => 'select',
            'options' => [
                0 => 'one_sighting',
                1 => 'occasional',
                2 => 'frequent',
                3 => 'severe',
            ],
        ],
        'has_pets' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'sighting_location' => 'Onde viu ou ouviu os roedores? Cozinha, sótão, cave?',
                'property_type' => 'Que tipo de propriedade é? Casa, apartamento, restaurante?',
                'infestation_level' => 'Com que frequência vê sinais? Foi só uma vez ou é frequente?',
                'has_pets' => 'Tem animais de estimação em casa?',
            ],
            'field_options' => [
                'sighting_location' => [
                    'kitchen' => 'Cozinha',
                    'attic' => 'Sótão',
                    'basement' => 'Cave',
                    'garden' => 'Jardim',
                    'garage' => 'Garagem',
                    'walls' => 'Paredes',
                    'multiple' => 'Vários locais',
                    'other' => 'Outro',
                ],
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'restaurant' => 'Restaurante',
                    'warehouse' => 'Armazém',
                    'office' => 'Escritório',
                    'other' => 'Outro',
                ],
                'infestation_level' => [
                    'one_sighting' => 'Um avistamento',
                    'occasional' => 'Ocasional',
                    'frequent' => 'Frequente',
                    'severe' => 'Grave — muitos',
                ],
                'has_pets' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                ],
            ],
            'name' => 'Controlo de Roedores',
            'keywords' => [
                0 => 'ratos',
                1 => 'ratazanas',
                2 => 'roedores',
                3 => 'rato',
                4 => 'ratazana',
                5 => 'barulho no teto',
                6 => 'barulho no tecto',
                7 => 'roeu',
                8 => 'excrementos',
            ],
            'synonyms' => [
                'sighting_location' => [
                    'kitchen' => [
                        0 => 'cozinha',
                        1 => 'dispensa',
                        2 => 'comida',
                        3 => 'armários',
                    ],
                    'attic' => [
                        0 => 'sótão',
                        1 => 'sotao',
                        2 => 'teto',
                        3 => 'tecto',
                        4 => 'telhado',
                        5 => 'forro',
                    ],
                    'basement' => [
                        0 => 'cave',
                        1 => 'garagem',
                        2 => 'subsolo',
                    ],
                    'garden' => [
                        0 => 'jardim',
                        1 => 'quintal',
                        2 => 'exterior',
                        3 => 'fora',
                    ],
                    'walls' => [
                        0 => 'paredes',
                        1 => 'entre paredes',
                        2 => 'roer',
                        3 => 'barulho',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para controlo de roedores. Recolhe informações sobre avistamentos e o tipo de propriedade. Sê discreto e profissional — o cliente pode estar envergonhado. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
