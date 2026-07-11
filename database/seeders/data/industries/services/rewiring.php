<?php

return [
    'key' => 'rewiring',
    'icon' => '🔌',
    'required_fields' => [
        0 => 'property_type',
        1 => 'wiring_age',
    ],
    'optional_fields' => [
        0 => 'full_or_partial',
        1 => 'occupancy',
        2 => 'certification_required',
    ],
    'field_definitions' => [
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
        'wiring_age' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_15',
                1 => '15_to_30',
                2 => 'over_30',
                3 => 'not_sure',
            ],
        ],
        'full_or_partial' => [
            'type' => 'select',
            'options' => [
                0 => 'full',
                1 => 'partial',
                2 => 'not_sure',
            ],
        ],
        'occupancy' => [
            'type' => 'select',
            'options' => [
                0 => 'occupied',
                1 => 'vacant',
                2 => 'moving_soon',
            ],
        ],
        'certification_required' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'unsure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'property_type' => 'Que tipo de propriedade precisa de requalificação?',
                'wiring_age' => 'Sabe quantos anos tem a instalação elétrica atual?',
                'full_or_partial' => 'Pretende requalificação total ou só parcial?',
                'occupancy' => 'A propriedade está habitada durante as obras?',
                'certification_required' => 'Precisa de certificado da instalação elétrica?',
            ],
            'field_options' => [
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'commercial' => 'Comercial',
                    'industrial' => 'Industrial',
                    'other' => 'Outro',
                ],
                'wiring_age' => [
                    'less_than_15' => '< 15 anos',
                    '15_to_30' => '15-30 anos',
                    'over_30' => '> 30 anos',
                    'not_sure' => 'Não sei',
                ],
                'full_or_partial' => [
                    'full' => 'Total',
                    'partial' => 'Parcial',
                    'not_sure' => 'Não sei/A avaliar',
                ],
                'occupancy' => [
                    'occupied' => 'Habitada',
                    'vacant' => 'Vazia',
                    'moving_soon' => 'Vai mudar-se',
                ],                'certification_required' => [
                    'yes' => 'Sim, preciso de certificado',
                    'no' => 'Não é necessário',
                    'unsure' => 'Não tenho a certeza',
                ],            ],
            'name' => 'Requaliﬁcação Elétrica',
            'keywords' => [
                0 => 'requalificação',
                1 => 'requalificacao',
                2 => 'trocar fios',
                3 => 'cablagem',
                4 => 'fiação',
                5 => 'fiacao',
                6 => 'renovar eletricidade',
                7 => 'certificado',
            ],
            'synonyms' => [],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para requalificação elétrica. Recolhe informações sobre a instalação atual. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
