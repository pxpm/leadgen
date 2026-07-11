<?php

return [
    'key' => 'solar_panels',
    'icon' => '☀️',
    'required_fields' => [
        0 => 'roof_type',
        1 => 'monthly_bill',
    ],
    'optional_fields' => [
        0 => 'ownership',
        1 => 'battery_interest',
        2 => 'panel_count_estimate',
    ],
    'field_definitions' => [
        'roof_type' => [
            'type' => 'select',
            'options' => [
                0 => 'tile',
                1 => 'flat',
                2 => 'metal',
                3 => 'slate',
                4 => 'other',
            ],
        ],
        'monthly_bill' => [
            'type' => 'select',
            'options' => [
                0 => 'under_50',
                1 => '50_to_100',
                2 => '100_to_200',
                3 => 'over_200',
            ],
        ],
        'ownership' => [
            'type' => 'select',
            'options' => [
                0 => 'owner',
                1 => 'renter',
                2 => 'business',
            ],
        ],
        'battery_interest' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'not_sure',
            ],
        ],
        'panel_count_estimate' => [
            'type' => 'select',
            'options' => [
                0 => '4_to_6',
                1 => '8_to_12',
                2 => '14_or_more',
                3 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'roof_type' => 'Que tipo de telhado tem? Telha, plano, metálico?',
                'monthly_bill' => 'Qual é o valor médio da sua fatura de eletricidade?',
                'ownership' => 'É proprietário do imóvel?',
                'battery_interest' => 'Tem interesse em baterias para armazenar energia?',
                'panel_count_estimate' => 'Tem ideia de quantos painéis precisa?',
            ],
            'field_options' => [
                'roof_type' => [
                    'tile' => 'Telha',
                    'flat' => 'Plano',
                    'metal' => 'Metálico',
                    'slate' => 'Ardósia',
                    'other' => 'Outro',
                ],
                'monthly_bill' => [
                    'under_50' => '< 50€',
                    '50_to_100' => '50-100€',
                    '100_to_200' => '100-200€',
                    'over_200' => '> 200€',
                ],
                'ownership' => [
                    'owner' => 'Proprietário',
                    'renter' => 'Inquilino',
                    'business' => 'Empresa',
                ],
                'battery_interest' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'not_sure' => 'Não sei',
                ],
                'panel_count_estimate' => [
                    '4_to_6' => '4-6',
                    '8_to_12' => '8-12',
                    '14_or_more' => '14+',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Painéis Solares',
            'keywords' => [
                0 => 'painéis solares',
                1 => 'paineis solares',
                2 => 'solar',
                3 => 'fotovoltaico',
                4 => 'autoconsumo',
                5 => 'bateria',
                6 => 'energia',
            ],
            'synonyms' => [
                'monthly_bill' => [
                    'under_50' => [
                        0 => 'pouco',
                        1 => '50',
                        2 => 'baixo',
                        3 => '40',
                        4 => '30',
                    ],
                    '50_to_100' => [
                        0 => 'médio',
                        1 => 'medio',
                        2 => '80',
                        3 => '70',
                        4 => '90',
                    ],
                    '100_to_200' => [
                        0 => 'alto',
                        1 => '150',
                        2 => '120',
                        3 => 'elevado',
                    ],
                    'over_200' => [
                        0 => 'muito alto',
                        1 => '200',
                        2 => '250',
                        3 => '300',
                        4 => 'gasto muito',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para instalação de painéis solares. Recolhe informações sobre o telhado e consumo energético. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
