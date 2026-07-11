<?php

return [
    'key' => 'termite_inspection',
    'icon' => '🔍',
    'required_fields' => [
        0 => 'property_age',
        1 => 'damage_visible',
    ],
    'optional_fields' => [
        0 => 'property_type',
        1 => 'previous_treatment',
    ],
    'field_definitions' => [
        'property_age' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_10',
                1 => '10_to_30',
                2 => 'over_30',
                3 => 'not_sure',
            ],
        ],
        'damage_visible' => [
            'type' => 'select',
            'options' => [
                0 => 'yes_wood_damage',
                1 => 'yes_insects_seen',
                2 => 'yes_both',
                3 => 'no_just_suspect',
                4 => 'not_sure',
            ],
        ],
        'property_type' => [
            'type' => 'select',
            'options' => [
                0 => 'apartment',
                1 => 'house',
                2 => 'commercial',
                3 => 'industrial',
                4 => 'other',
            ],
        ],
        'previous_treatment' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'property_age' => 'Sabe a idade aproximada da propriedade?',
                'damage_visible' => 'Viu algum sinal? Madeira danificada, insetos, pó de madeira?',
                'property_type' => 'Que tipo de propriedade é?',
                'previous_treatment' => 'Já fez algum tratamento anti-térmitas anteriormente?',
            ],
            'field_options' => [
                'property_age' => [
                    'less_than_10' => '< 10 anos',
                    '10_to_30' => '10-30 anos',
                    'over_30' => '> 30 anos',
                    'not_sure' => 'Não sei',
                ],
                'damage_visible' => [
                    'yes_wood_damage' => 'Sim, madeira danificada',
                    'yes_insects_seen' => 'Sim, vi insetos',
                    'yes_both' => 'Sim, ambos',
                    'no_just_suspect' => 'Não, só desconfio',
                    'not_sure' => 'Não tenho a certeza',
                ],
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'commercial' => 'Comercial',
                    'industrial' => 'Industrial',
                    'other' => 'Outro',
                ],
                'previous_treatment' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Inspeção de Térmitas',
            'keywords' => [
                0 => 'térmitas',
                1 => 'termitas',
                2 => 'formiga branca',
                3 => 'madeira',
                4 => 'caruncho',
                5 => 'traça madeira',
                6 => 'traca madeira',
                7 => 'madeira oca',
            ],
            'synonyms' => [
                'damage_visible' => [
                    'yes_wood_damage' => [
                        0 => 'madeira',
                        1 => 'comida',
                        2 => 'roída',
                        3 => 'roida',
                        4 => 'oca',
                        5 => 'oco',
                        6 => 'pó',
                        7 => 'po',
                        8 => 'serrim',
                    ],
                    'yes_insects_seen' => [
                        0 => 'vi',
                        1 => 'apareceram',
                        2 => 'asas',
                        3 => 'enxame',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para inspeção de térmitas e caruncho. Recolhe informações sobre a propriedade e danos visíveis. Sê profissional e informativo. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
