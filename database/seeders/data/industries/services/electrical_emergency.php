<?php

return [
    'key' => 'electrical_emergency',
    'icon' => '⚡',
    'required_fields' => [
        0 => 'emergency_type',
        1 => 'affected_area',
    ],
    'optional_fields' => [
        0 => 'property_age',
    ],
    'field_definitions' => [
        'emergency_type' => [
            'type' => 'select',
            'options' => [
                0 => 'total_blackout',
                1 => 'partial_blackout',
                2 => 'sparks',
                3 => 'burning_smell',
                4 => 'breaker_trip',
                5 => 'other',
            ],
        ],
        'affected_area' => [
            'type' => 'select',
            'options' => [
                0 => 'whole_house',
                1 => 'one_room',
                2 => 'one_circuit',
                3 => 'outdoor',
                4 => 'other',
            ],
        ],
        'property_age' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_10',
                1 => '10_to_30',
                2 => 'over_30',
                3 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [
    ],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'emergency_type' => 'O que aconteceu? Ficou sem luz, há faíscas, cheiro a queimado?',
                'affected_area' => 'A avaria afeta a casa toda ou só uma parte?',
                'property_age' => 'Qual a idade aproximada da instalação elétrica?',
            ],
            'field_options' => [
                'emergency_type' => [
                    'total_blackout' => 'Falha total',
                    'partial_blackout' => 'Falha parcial',
                    'sparks' => 'Faíscas',
                    'burning_smell' => 'Cheiro a queimado',
                    'breaker_trip' => 'Disjuntor dispara',
                    'other' => 'Outro',
                ],
                'affected_area' => [
                    'whole_house' => 'Casa toda',
                    'one_room' => 'Uma divisão',
                    'one_circuit' => 'Um circuito',
                    'outdoor' => 'Exterior',
                    'other' => 'Outro',
                ],
                'property_age' => [
                    'less_than_10' => '< 10 anos',
                    '10_to_30' => '10-30 anos',
                    'over_30' => '> 30 anos',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Emergência Elétrica',
            'keywords' => [
                0 => 'curto-circuito',
                1 => 'curto circuito',
                2 => 'sem luz',
                3 => 'sem eletricidade',
                4 => 'faísca',
                5 => 'faisca',
                6 => 'cheiro queimado',
                7 => 'disjuntor',
                8 => 'quadro',
            ],
            'synonyms' => [
                'emergency_type' => [
                    'total_blackout' => [
                        0 => 'sem luz',
                        1 => 'tudo apagado',
                        2 => 'casa toda',
                        3 => 'geral',
                    ],
                    'partial_blackout' => [
                        0 => 'uma parte',
                        1 => 'umas luzes',
                        2 => 'só aqui',
                        3 => 'sala',
                        4 => 'cozinha',
                    ],
                    'sparks' => [
                        0 => 'faísca',
                        1 => 'faisca',
                        2 => 'fagulha',
                        3 => 'estalido',
                    ],
                    'burning_smell' => [
                        0 => 'cheiro',
                        1 => 'queimado',
                        2 => 'fumo',
                        3 => 'plástico',
                        4 => 'plastico',
                    ],
                    'breaker_trip' => [
                        0 => 'disjuntor',
                        1 => 'dispara',
                        2 => 'vai abaixo',
                        3 => 'quadro',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para emergências elétricas. Recolhe informações sobre o problema com segurança — o cliente pode estar em pânico. Sê calmo, conversador e profissional. Faz uma pergunta de cada vez. NUNCA dês estimativas de custos.

SEMPRE adiciona no FINAL um bloco JSON: emergency_type, affected_area, property_age, urgency',
            ],
        ],
    ],
];
