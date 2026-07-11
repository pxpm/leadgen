<?php

return [
    'key' => 'insect_control',
    'icon' => '🪳',
    'required_fields' => [
        0 => 'insect_type',
        1 => 'property_type',
    ],
    'optional_fields' => [
        0 => 'infestation_level',
        1 => 'location',
    ],
    'field_definitions' => [
        'insect_type' => [
            'type' => 'select',
            'options' => [
                0 => 'cockroaches',
                1 => 'ants',
                2 => 'spiders',
                3 => 'moths',
                4 => 'silverfish',
                5 => 'fleas',
                6 => 'multiple',
                7 => 'not_sure',
            ],
        ],
        'property_type' => [
            'type' => 'select',
            'options' => [
                0 => 'apartment',
                1 => 'house',
                2 => 'restaurant',
                3 => 'hotel',
                4 => 'office',
                5 => 'other',
            ],
        ],
        'infestation_level' => [
            'type' => 'select',
            'options' => [
                0 => 'few',
                1 => 'noticeable',
                2 => 'severe',
            ],
        ],
        'location' => [
            'type' => 'select',
            'options' => [
                0 => 'kitchen',
                1 => 'bathroom',
                2 => 'bedroom',
                3 => 'living_room',
                4 => 'basement',
                5 => 'outdoor',
                6 => 'whole_house',
                7 => 'other',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'insect_type' => 'Que tipo de insetos tem visto? Baratas, formigas, aranhas, traças?',
                'property_type' => 'Que tipo de propriedade é?',
                'infestation_level' => 'É algo pontual ou uma infestação generalizada?',
                'location' => 'Em que divisões da casa aparecem mais?',
            ],
            'field_options' => [
                'insect_type' => [
                    'cockroaches' => 'Baratas',
                    'ants' => 'Formigas',
                    'spiders' => 'Aranhas',
                    'moths' => 'Traças',
                    'silverfish' => 'Peixinhos de prata',
                    'fleas' => 'Pulgas',
                    'multiple' => 'Vários tipos',
                    'not_sure' => 'Não sei identificar',
                ],
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'restaurant' => 'Restaurante',
                    'hotel' => 'Hotel',
                    'office' => 'Escritório',
                    'other' => 'Outro',
                ],
                'infestation_level' => [
                    'few' => 'Pontual',
                    'noticeable' => 'Moderado',
                    'severe' => 'Grave',
                ],
                'location' => [
                    'kitchen' => 'Cozinha',
                    'bathroom' => 'Casa de banho',
                    'bedroom' => 'Quarto',
                    'living_room' => 'Sala',
                    'basement' => 'Cave',
                    'outdoor' => 'Exterior',
                    'whole_house' => 'Casa toda',
                    'other' => 'Outro',
                ],
            ],
            'name' => 'Controlo de Insetos',
            'keywords' => [
                0 => 'baratas',
                1 => 'formigas',
                2 => 'traça',
                3 => 'traca',
                4 => 'aranhas',
                5 => 'aranha',
                6 => 'insetos',
                7 => 'bichos',
                8 => 'infestação',
                9 => 'infestacao',
            ],
            'synonyms' => [
                'insect_type' => [
                    'cockroaches' => [
                        0 => 'barata',
                        1 => 'baratas',
                        2 => 'cucaracha',
                    ],
                    'ants' => [
                        0 => 'formiga',
                        1 => 'formigas',
                        2 => 'formigueiro',
                    ],
                    'spiders' => [
                        0 => 'aranha',
                        1 => 'aranhas',
                        2 => 'aranhão',
                    ],
                    'moths' => [
                        0 => 'traça',
                        1 => 'traca',
                        2 => 'traças',
                    ],
                    'silverfish' => [
                        0 => 'peixinho',
                        1 => 'prata',
                        2 => 'bicho da prata',
                    ],
                    'fleas' => [
                        0 => 'pulga',
                        1 => 'pulgas',
                    ],
                ],
                'infestation_level' => [
                    'few' => [
                        0 => 'algumas',
                        1 => 'poucas',
                        2 => 'uma ou outra',
                        3 => 'de vez em quando',
                    ],
                    'noticeable' => [
                        0 => 'bastantes',
                        1 => 'muitas',
                        2 => 'todos os dias',
                    ],
                    'severe' => [
                        0 => 'praga',
                        1 => 'infestação',
                        2 => 'infestado',
                        3 => 'não aguento mais',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para controlo de insetos. Recolhe informações sobre o tipo de inseto e nível de infestação. Sê discreto e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
