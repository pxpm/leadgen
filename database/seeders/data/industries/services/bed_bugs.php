<?php

return [
    'key' => 'bed_bugs',
    'icon' => '🛏️',
    'required_fields' => [
        0 => 'discovery_date',
        1 => 'rooms_affected',
    ],
    'optional_fields' => [
        0 => 'recent_travel',
        1 => 'bites_present',
        2 => 'mattress_age',
    ],
    'field_definitions' => [
        'discovery_date' => [
            'type' => 'select',
            'options' => [
                0 => 'today',
                1 => 'this_week',
                2 => 'this_month',
                3 => 'longer',
            ],
        ],
        'rooms_affected' => [
            'type' => 'select',
            'options' => [
                0 => 'one_bedroom',
                1 => 'multiple_bedrooms',
                2 => 'living_room',
                3 => 'whole_house',
                4 => 'other',
            ],
        ],
        'recent_travel' => [
            'type' => 'select',
            'options' => [
                0 => 'yes_hotel',
                1 => 'yes_abroad',
                2 => 'yes_other',
                3 => 'no',
            ],
        ],
        'bites_present' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'not_sure',
            ],
        ],
        'mattress_age' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_1',
                1 => '1_to_5',
                2 => 'over_5',
                3 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'discovery_date' => 'Quando notou os primeiros sinais? Hoje, esta semana, há mais tempo?',
                'rooms_affected' => 'Quais as divisões afetadas? Quarto, sala, casa toda?',
                'recent_travel' => 'Esteve recentemente num hotel ou viajou para o estrangeiro?',
                'bites_present' => 'Tem picadas na pele?',
                'mattress_age' => 'Que idade tem o colchão?',
            ],
            'field_options' => [
                'discovery_date' => [
                    'today' => 'Hoje',
                    'this_week' => 'Esta semana',
                    'this_month' => 'Este mês',
                    'longer' => 'Há mais tempo',
                ],
                'rooms_affected' => [
                    'one_bedroom' => 'Um quarto',
                    'multiple_bedrooms' => 'Vários quartos',
                    'living_room' => 'Sala',
                    'whole_house' => 'Casa toda',
                    'other' => 'Outro',
                ],
                'recent_travel' => [
                    'yes_hotel' => 'Sim, hotel',
                    'yes_abroad' => 'Sim, estrangeiro',
                    'yes_other' => 'Sim, outro',
                    'no' => 'Não',
                ],
                'bites_present' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'not_sure' => 'Não sei',
                ],
                'mattress_age' => [
                    'less_than_1' => '< 1 ano',
                    '1_to_5' => '1-5 anos',
                    'over_5' => '> 5 anos',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Tratamento de Percevejos',
            'keywords' => [
                0 => 'percevejos',
                1 => 'bed bugs',
                2 => 'picadas',
                3 => 'mordidelas',
                4 => 'colchão',
                5 => 'colchao',
                6 => 'cama',
                7 => 'quartos',
                8 => 'viajei',
            ],
            'synonyms' => [],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para tratamento de percevejos. Recolhe informações com sensibilidade — o cliente pode estar muito incomodado. Sê empático e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
