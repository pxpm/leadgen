<?php

return [
    'key' => 'drain_cleaning',
    'icon' => '🚿',
    'required_fields' => [
        0 => 'clog_location',
        1 => 'recurring',
    ],
    'optional_fields' => [
        0 => 'diy_attempted',
        1 => 'property_type',
        2 => 'water_source',
        3 => 'problem_start',
    ],
    'field_definitions' => [
        'clog_location' => [
            'type' => 'select',
            'options' => [
                0 => 'kitchen_sink',
                1 => 'bathroom_sink',
                2 => 'shower',
                3 => 'toilet',
                4 => 'main_sewer',
                5 => 'outdoor',
                6 => 'other',
            ],
        ],
        'recurring' => [
            'type' => 'select',
            'options' => [
                0 => 'first_time',
                1 => 'occasional',
                2 => 'frequent',
            ],
        ],
        'diy_attempted' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
            ],
        ],
        'property_type' => [
            'type' => 'select',
            'options' => [
                0 => 'apartment',
                1 => 'house',
                2 => 'commercial',
                3 => 'other',
            ],
        ],
        'water_source' => [
            'type' => 'select',
            'options' => [
                0 => 'municipal',
                1 => 'well',
                2 => 'septic',
                3 => 'both',
                4 => 'unsure',
            ],
        ],
        'problem_start' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_week',
                1 => 'few_weeks',
                2 => 'month_or_more',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'clog_location' => 'Onde está o entupimento? Pia, chuveiro, sanita, esgoto principal?',
                'recurring' => 'É a primeira vez que acontece ou é recorrente?',
                'diy_attempted' => 'Já tentou desentupir com algum produto ou ferramenta?',
                'property_type' => 'Que tipo de propriedade é?',
                'water_source' => 'O entupimento é na rede municipal ou em fossa séptica?',
                'problem_start' => 'Quando é que este problema começou?',
            ],
            'field_options' => [
                'clog_location' => [
                    'kitchen_sink' => 'Pia da cozinha',
                    'bathroom_sink' => 'Lavatório',
                    'shower' => 'Chuveiro',
                    'toilet' => 'Sanita',
                    'main_sewer' => 'Esgoto principal',
                    'outdoor' => 'Exterior',
                    'other' => 'Outro',
                ],
                'recurring' => [
                    'first_time' => 'Primeira vez',
                    'occasional' => 'Ocasional',
                    'frequent' => 'Frequente',
                ],
                'diy_attempted' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                ],
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'commercial' => 'Comercial',
                    'other' => 'Outro',
                ],
                'water_source' => [
                    'municipal' => 'Rede municipal',
                    'well' => 'Poço',
                    'septic' => 'Fossa séptica',
                    'both' => 'Ambos',
                    'unsure' => 'Não tenho a certeza',
                ],
                'problem_start' => [
                    'less_than_week' => 'Há menos de uma semana',
                    'few_weeks' => 'Há algumas semanas',
                    'month_or_more' => 'Há um mês ou mais',
                ],
            ],
            'name' => 'Desentupimentos',
            'keywords' => [
                0 => 'desentupir',
                1 => 'entupido',
                2 => 'entupimento',
                3 => 'esgoto',
                4 => 'ralo',
                5 => 'sanita',
                6 => 'pia',
                7 => 'não escoa',
                8 => 'nao escoa',
            ],
            'synonyms' => [
                'clog_location' => [
                    'kitchen_sink' => [
                        0 => 'pia',
                        1 => 'lava-louça',
                        2 => 'lava louça',
                        3 => 'cozinha',
                    ],
                    'bathroom_sink' => [
                        0 => 'lavatório',
                        1 => 'lavatorio',
                        2 => 'casa de banho',
                        3 => 'wc',
                    ],
                    'shower' => [
                        0 => 'chuveiro',
                        1 => 'banheira',
                        2 => 'base de duche',
                        3 => 'poliban',
                    ],
                    'toilet' => [
                        0 => 'sanita',
                        1 => 'retrete',
                        2 => 'vaso',
                    ],
                    'main_sewer' => [
                        0 => 'esgoto',
                        1 => 'coletor',
                        2 => 'coletora',
                        3 => 'principal',
                        4 => 'rua',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para desentupimentos. Recolhe informações sobre a localização e recorrência. Sê conversador e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
