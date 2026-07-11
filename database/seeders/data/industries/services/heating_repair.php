<?php

return [
    'key' => 'heating_repair',
    'icon' => '🔥',
    'required_fields' => [
        0 => 'heater_type',
        1 => 'problem_symptom',
    ],
    'optional_fields' => [
        0 => 'fuel_source',
        1 => 'unit_age',
    ],
    'field_definitions' => [
        'heater_type' => [
            'type' => 'select',
            'options' => [
                0 => 'boiler',
                1 => 'heat_pump',
                2 => 'furnace',
                3 => 'radiator',
                4 => 'water_heater',
                5 => 'other',
            ],
        ],
        'problem_symptom' => [
            'type' => 'select',
            'options' => [
                0 => 'no_heat',
                1 => 'intermittent',
                2 => 'noisy',
                3 => 'leaking',
                4 => 'smell',
                5 => 'other',
            ],
        ],
        'fuel_source' => [
            'type' => 'select',
            'options' => [
                0 => 'gas',
                1 => 'electric',
                2 => 'diesel',
                3 => 'pellet',
                4 => 'other',
            ],
        ],
        'unit_age' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_2',
                1 => '2_to_5',
                2 => '5_to_10',
                3 => 'over_10',
                4 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [
    ],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'heater_type' => 'Que tipo de sistema de aquecimento tem? Caldeira, bomba de calor, radiadores?',
                'problem_symptom' => 'O que se passa? Não aquece, funciona mal, faz barulho?',
                'fuel_source' => 'Qual é a fonte de energia? Gás, eletricidade, gasóleo?',
                'unit_age' => 'Há quanto tempo tem o sistema?',
            ],
            'field_options' => [
                'heater_type' => [
                    'boiler' => 'Caldeira',
                    'heat_pump' => 'Bomba de calor',
                    'furnace' => 'Fornalha',
                    'radiator' => 'Radiadores',
                    'water_heater' => 'Esquentador',
                    'other' => 'Outro',
                ],
                'problem_symptom' => [
                    'no_heat' => 'Não aquece',
                    'intermittent' => 'Funciona mal',
                    'noisy' => 'Barulho',
                    'leaking' => 'Fuga de água',
                    'smell' => 'Cheiro',
                    'other' => 'Outro',
                ],
                'fuel_source' => [
                    'gas' => 'Gás',
                    'electric' => 'Elétrico',
                    'diesel' => 'Gasóleo',
                    'pellet' => 'Pellets',
                    'other' => 'Outro',
                ],
                'unit_age' => [
                    'less_than_2' => '< 2 anos',
                    '2_to_5' => '2-5 anos',
                    '5_to_10' => '5-10 anos',
                    'over_10' => '> 10 anos',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Reparação de Aquecimento',
            'keywords' => [
                0 => 'aquecimento',
                1 => 'caldeira',
                2 => 'esquentador',
                3 => 'radiador',
                4 => 'não aquece',
                5 => 'nao aquece',
                6 => 'bomba de calor',
            ],
            'synonyms' => [
                'heater_type' => [
                    'boiler' => [
                        0 => 'caldeira',
                        1 => 'central',
                        2 => 'aquecimento central',
                    ],
                    'heat_pump' => [
                        0 => 'bomba de calor',
                        1 => 'bomba calor',
                    ],
                    'furnace' => [
                        0 => 'fornalha',
                        1 => 'a gás',
                        2 => 'a gas',
                    ],
                    'radiator' => [
                        0 => 'radiador',
                        1 => 'radiadores',
                        2 => 'termossifão',
                        3 => 'termossifao',
                    ],
                    'water_heater' => [
                        0 => 'esquentador',
                        1 => 'termoacumulador',
                        2 => 'cilindro',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para reparação de aquecimento. Recolhe informações sobre o sistema e a avaria. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. NUNCA dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON:
- heater_type: boiler, heat_pump, furnace, radiator, water_heater, other
- problem_symptom: no_heat, intermittent, noisy, leaking, smell, other
- fuel_source: gas, electric, diesel, pellet, other
- unit_age: less_than_2, 2_to_5, 5_to_10, over_10, not_sure
- urgency: emergency, within_week, within_month',
            ],
        ],
    ],
];
