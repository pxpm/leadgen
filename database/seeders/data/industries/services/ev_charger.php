<?php

return [
    'key' => 'ev_charger',
    'icon' => '🚗',
    'required_fields' => [
        0 => 'charger_type',
        1 => 'parking_type',
    ],
    'optional_fields' => [
        0 => 'car_model',
        1 => 'power_available',
    ],
    'field_definitions' => [
        'charger_type' => [
            'type' => 'select',
            'options' => [
                0 => 'wallbox',
                1 => 'portable',
                2 => 'fast',
                3 => 'not_sure',
            ],
        ],
        'parking_type' => [
            'type' => 'select',
            'options' => [
                0 => 'garage',
                1 => 'driveway',
                2 => 'street',
                3 => 'condo_garage',
                4 => 'other',
            ],
        ],
        'car_model' => [
            'type' => 'text',
        ],
        'power_available' => [
            'type' => 'select',
            'options' => [
                0 => '3_45',
                1 => '6_9',
                2 => '10_35',
                3 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'charger_type' => 'Que tipo de carregador pretende? Wallbox fixa, portátil, carregador rápido?',
                'parking_type' => 'Onde estaciona o carro? Garagem, entrada, rua?',
                'car_model' => 'Qual é o modelo do carro? (opcional)',
                'power_available' => 'Sabe a potência contratada? 3.45, 6.9, 10.35 kVA?',
            ],
            'field_options' => [
                'charger_type' => [
                    'wallbox' => 'Wallbox (fixa)',
                    'portable' => 'Portátil',
                    'fast' => 'Rápido DC',
                    'not_sure' => 'Não sei',
                ],
                'parking_type' => [
                    'garage' => 'Garagem',
                    'driveway' => 'Entrada',
                    'street' => 'Rua',
                    'condo_garage' => 'Garagem de prédio',
                    'other' => 'Outro',
                ],
                'power_available' => [
                    '3_45' => '3.45 kVA',
                    '6_9' => '6.9 kVA',
                    '10_35' => '10.35 kVA',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Carregadores EV',
            'keywords' => [
                0 => 'carregador',
                1 => 'carro elétrico',
                2 => 'carro eletrico',
                3 => 'ev',
                4 => 'wallbox',
                5 => 'tomada',
                6 => 'tesla',
                7 => 'carregamento',
            ],
            'synonyms' => [
                'charger_type' => [
                    'wallbox' => [
                        0 => 'wallbox',
                        1 => 'parede',
                        2 => 'fixo',
                        3 => 'instalado',
                    ],
                    'portable' => [
                        0 => 'portátil',
                        1 => 'portatil',
                        2 => 'móvel',
                        3 => 'movel',
                        4 => 'tomada normal',
                    ],
                    'fast' => [
                        0 => 'rápido',
                        1 => 'rapido',
                        2 => 'dc',
                        3 => '50kw',
                        4 => 'posto',
                    ],
                ],
                'parking_type' => [
                    'garage' => [
                        0 => 'garagem',
                        1 => 'fechada',
                        2 => 'box',
                    ],
                    'driveway' => [
                        0 => 'entrada',
                        1 => 'casa',
                        2 => 'portão',
                        3 => 'portao',
                    ],
                    'street' => [
                        0 => 'rua',
                        1 => 'passeio',
                        2 => 'estacionamento',
                    ],
                    'condo_garage' => [
                        0 => 'condomínio',
                        1 => 'condominio',
                        2 => 'prédio',
                        3 => 'predio',
                        4 => 'coletiva',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para instalação de carregadores de veículos elétricos. Recolhe informações sobre o local e o carro. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
