<?php

return [
    'key' => 'water_heater',
    'icon' => '🛁',
    'required_fields' => [
        0 => 'heater_type',
        1 => 'problem_type',
    ],
    'optional_fields' => [
        0 => 'heater_age',
        1 => 'fuel_type',
        2 => 'problem_start',
    ],
    'field_definitions' => [
        'heater_type' => [
            'type' => 'select',
            'options' => [
                0 => 'tankless_gas',
                1 => 'tankless_electric',
                2 => 'tank',
                3 => 'heat_pump',
                4 => 'solar',
                5 => 'other',
            ],
        ],
        'problem_type' => [
            'type' => 'select',
            'options' => [
                0 => 'no_hot_water',
                1 => 'not_hot_enough',
                2 => 'leaking',
                3 => 'noisy',
                4 => 'replace',
                5 => 'install_new',
                6 => 'other',
            ],
        ],
        'heater_age' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_2',
                1 => '2_to_5',
                2 => '5_to_10',
                3 => 'over_10',
                4 => 'not_sure',
            ],
        ],
        'fuel_type' => [
            'type' => 'select',
            'options' => [
                0 => 'natural_gas',
                1 => 'bottled_gas',
                2 => 'electric',
                3 => 'other',
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
                'heater_type' => 'Que tipo de equipamento tem? Esquentador a gás, termoacumulador, bomba de calor?',
                'problem_type' => 'O que se passa? Não tem água quente, está com fugas, quer substituir?',
                'heater_age' => 'Há quanto tempo tem o equipamento?',
                'fuel_type' => 'Qual é a alimentação? Gás natural, botija, eletricidade?',
                'problem_start' => 'Quando é que este problema começou?',
            ],
            'field_options' => [
                'heater_type' => [
                    'tankless_gas' => 'Esquentador a gás',
                    'tankless_electric' => 'Esquentador elétrico',
                    'tank' => 'Termoacumulador',
                    'heat_pump' => 'Bomba de calor',
                    'solar' => 'Solar',
                    'other' => 'Outro',
                ],
                'problem_type' => [
                    'no_hot_water' => 'Sem água quente',
                    'not_hot_enough' => 'Não aquece bem',
                    'leaking' => 'Fuga',
                    'noisy' => 'Barulho',
                    'replace' => 'Substituir',
                    'install_new' => 'Instalar novo',
                    'other' => 'Outro',
                ],
                'heater_age' => [
                    'less_than_2' => '< 2 anos',
                    '2_to_5' => '2-5 anos',
                    '5_to_10' => '5-10 anos',
                    'over_10' => '> 10 anos',
                    'not_sure' => 'Não sei',
                ],
                'fuel_type' => [
                    'natural_gas' => 'Gás natural',
                    'bottled_gas' => 'Botija',
                    'electric' => 'Elétrico',
                    'other' => 'Outro',
                ],
                'problem_start' => [
                    'less_than_week' => 'Há menos de uma semana',
                    'few_weeks' => 'Há algumas semanas',
                    'month_or_more' => 'Há um mês ou mais',
                ],
            ],
            'name' => 'Esquentadores e Termoacumuladores',
            'keywords' => [
                0 => 'esquentador',
                1 => 'termoacumulador',
                2 => 'cilindro',
                3 => 'água quente',
                4 => 'agua quente',
                5 => 'banho frio',
                6 => 'não aquece água',
            ],
            'synonyms' => [
                'heater_type' => [
                    'tankless_gas' => [
                        0 => 'esquentador',
                        1 => 'a gás',
                        2 => 'a gas',
                        3 => 'gás',
                        4 => 'gas',
                    ],
                    'tankless_electric' => [
                        0 => 'elétrico',
                        1 => 'eletrico',
                        2 => 'instantâneo',
                    ],
                    'tank' => [
                        0 => 'termoacumulador',
                        1 => 'cilindro',
                        2 => 'acumulador',
                        3 => 'depósito',
                        4 => 'deposito',
                    ],
                    'heat_pump' => [
                        0 => 'bomba de calor',
                        1 => 'termodinâmico',
                        2 => 'termodinamico',
                    ],
                    'solar' => [
                        0 => 'solar',
                        1 => 'painel',
                        2 => 'sol',
                        3 => 'térmico',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para equipamentos de água quente. Recolhe informações sobre o equipamento e o problema. Sê conversador e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
